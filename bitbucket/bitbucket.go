package bitbucket

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"strings"

	git "gopkg.in/src-d/go-git.v4"
	"gopkg.in/src-d/go-git.v4/config"
	"gopkg.in/src-d/go-git.v4/plumbing"
	githttp "gopkg.in/src-d/go-git.v4/plumbing/transport/http"
)

// baseURL is the Bitbucket API base URL (for Bitbucket Cloud).
const baseURL = "https://api.bitbucket.org/2.0"

// BitbucketClient holds your Bitbucket authentication information.
type BitbucketClient struct {
	Username    string
	AppPassword string
}

// DownloadRepository clones a repository from Bitbucket. If the repository is empty
// (i.e. no branch is provided or no HEAD exists), it initializes a new local repository
// and adds the remote "origin". Use this when you have an empty repository on Bitbucket.
func (client *BitbucketClient) DownloadRepository(workspace, repoSlug, branch, destination string) error {
	cloneURL := fmt.Sprintf("https://%s:%s@bitbucket.org/%s/%s.git",
		client.Username, client.AppPassword, workspace, repoSlug)
	fmt.Println("Cloning from:", cloneURL)

	// If no branch is provided, assume the repository is empty and initialize locally.
	if branch == "" {
		fmt.Println("No branch provided; assuming remote repository is empty. Initializing local repository.")
		repo, err := git.PlainInit(destination, false)
		if err != nil {
			return fmt.Errorf("failed to initialize repository: %w", err)
		}
		_, err = repo.CreateRemote(&config.RemoteConfig{
			Name: "origin",
			URLs: []string{cloneURL},
		})
		if err != nil {
			return fmt.Errorf("failed to add remote: %w", err)
		}
		return nil
	}

	// Otherwise, set up clone options with the provided branch.
	cloneOptions := &git.CloneOptions{
		URL:           cloneURL,
		SingleBranch:  true,
		Depth:         1,
		ReferenceName: plumbing.ReferenceName(fmt.Sprintf("refs/heads/%s", branch)),
	}

	// Attempt to clone the repository.
	_, err := git.PlainClone(destination, false, cloneOptions)
	if err != nil {
		// If error indicates no HEAD (i.e. empty repo), then initialize locally.
		if strings.Contains(err.Error(), "couldn't find remote ref HEAD") {
			fmt.Println("Remote repository is empty; initializing local repository instead.")
			repo, initErr := git.PlainInit(destination, false)
			if initErr != nil {
				return fmt.Errorf("failed to initialize repository: %w", initErr)
			}
			_, remoteErr := repo.CreateRemote(&config.RemoteConfig{
				Name: "origin",
				URLs: []string{cloneURL},
			})
			if remoteErr != nil {
				return fmt.Errorf("failed to add remote: %w", remoteErr)
			}
			return nil
		}
		return fmt.Errorf("failed to clone repository: %w", err)
	}

	return nil
}

// CreateProjectOrUseExist checks if a project exists by its key in the given workspace.
// If it doesn't exist, it creates a new project using the given name.
func (client *BitbucketClient) CreateProjectOrUseExist(workspace, projectKey, projectName string) (map[string]interface{}, error) {
	project, err := client.getProjectByKey(workspace, projectKey)
	if err == nil {
		return project, nil
	}

	payload := map[string]interface{}{
		"key":  projectKey,
		"name": projectName,
	}
	payloadBytes, err := json.Marshal(payload)
	if err != nil {
		return nil, err
	}

	url := fmt.Sprintf("%s/workspaces/%s/projects", baseURL, workspace)
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(payloadBytes))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.SetBasicAuth(client.Username, client.AppPassword)

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusCreated {
		bodyBytes, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("failed to create project: status %d, body: %s", resp.StatusCode, string(bodyBytes))
	}

	var projectResp map[string]interface{}
	if err := json.NewDecoder(resp.Body).Decode(&projectResp); err != nil {
		return nil, err
	}

	return projectResp, nil
}

// getProjectByKey retrieves a project from a workspace using its key.
func (client *BitbucketClient) getProjectByKey(workspace, projectKey string) (map[string]interface{}, error) {
	url := fmt.Sprintf("%s/workspaces/%s/projects/%s", baseURL, workspace, projectKey)
	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return nil, err
	}
	req.SetBasicAuth(client.Username, client.AppPassword)

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode == http.StatusNotFound {
		return nil, errors.New("project not found")
	}
	if resp.StatusCode != http.StatusOK {
		bodyBytes, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("failed to get project: status %d, body: %s", resp.StatusCode, string(bodyBytes))
	}

	var projectResp map[string]interface{}
	if err := json.NewDecoder(resp.Body).Decode(&projectResp); err != nil {
		return nil, err
	}

	return projectResp, nil
}

// CreateRepository creates a new repository within the specified workspace and associates it with a project.
func (client *BitbucketClient) CreateRepository(workspace, repoSlug, projectKey string, isPrivate bool) (map[string]interface{}, error) {
	payload := map[string]interface{}{
		"scm": "git",
		"project": map[string]interface{}{
			"key": projectKey,
		},
		"is_private": isPrivate,
	}
	payloadBytes, err := json.Marshal(payload)
	if err != nil {
		return nil, err
	}

	url := fmt.Sprintf("%s/repositories/%s/%s", baseURL, workspace, repoSlug)
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(payloadBytes))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.SetBasicAuth(client.Username, client.AppPassword)

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusCreated && resp.StatusCode != http.StatusOK {
		bodyBytes, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("failed to create repository: status %d, body: %s", resp.StatusCode, string(bodyBytes))
	}

	var repoResp map[string]interface{}
	if err := json.NewDecoder(resp.Body).Decode(&repoResp); err != nil {
		return nil, err
	}

	return repoResp, nil
}

// PushRepository opens the local Git repository at the given folder path and pushes
// its commits to the remote "origin" using the BitbucketClient's credentials.
func (client *BitbucketClient) PushRepository(folderPath string) error {
	repo, err := git.PlainOpen(folderPath)
	if err != nil {
		return fmt.Errorf("failed to open repository at %s: %w", folderPath, err)
	}

	pushOptions := &git.PushOptions{
		RemoteName: "origin",
		Auth: &githttp.BasicAuth{
			Username: client.Username,
			Password: client.AppPassword,
		},
	}

	err = repo.Push(pushOptions)
	if err != nil {
		if err == git.NoErrAlreadyUpToDate {
			fmt.Println("Everything is already up-to-date.")
			return nil
		}
		return fmt.Errorf("failed to push repository: %w", err)
	}

	fmt.Println("Push successful!")
	return nil
}
