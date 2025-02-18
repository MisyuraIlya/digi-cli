package bitbucket

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"

	git "gopkg.in/src-d/go-git.v4"
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

// DownloadRepository clones a repository using git clone.
// Parameters:
// - workspace: The Bitbucket workspace (team) name.
// - repoSlug: The repository slug (name).
// - branch: The branch to clone (e.g., "main" or "master").
// - destination: The local directory where the repository will be cloned.
func (client *BitbucketClient) DownloadRepository(workspace, repoSlug, branch, destination string) error {
	// Construct the HTTPS clone URL with embedded credentials.
	cloneURL := fmt.Sprintf("https://%s:%s@bitbucket.org/%s/%s.git",
		client.Username, client.AppPassword, workspace, repoSlug)
	fmt.Println("Cloning from:", cloneURL)

	// Define clone options.
	cloneOptions := &git.CloneOptions{
		URL:           cloneURL,
		ReferenceName: plumbing.ReferenceName(fmt.Sprintf("refs/heads/%s", branch)),
		SingleBranch:  true,
		Depth:         1, // Shallow clone; remove or adjust if full history is needed.
	}

	// Clone the repository into the destination directory.
	_, err := git.PlainClone(destination, false, cloneOptions)
	if err != nil {
		return fmt.Errorf("failed to clone repository: %w", err)
	}

	return nil
}

// CreateProjectOrUseExist checks if a project exists by its key in the given workspace.
// If it doesn't exist, it creates a new project using the given name.
// Returns the project details (as a map) or an error.
func (client *BitbucketClient) CreateProjectOrUseExist(workspace, projectKey, projectName string) (map[string]interface{}, error) {
	// Attempt to retrieve the project by its key.
	project, err := client.getProjectByKey(workspace, projectKey)
	if err == nil {
		// Project exists; return it.
		return project, nil
	}

	// Project does not exist; create it.
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
// Parameters:
// - workspace: The Bitbucket workspace name.
// - repoSlug: The repository slug (desired name for the repository).
// - projectKey: The project key to which the repository should belong.
// - isPrivate: Set to true for a private repository.
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

	if resp.StatusCode != http.StatusCreated {
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
// It assumes that the repository's remote "origin" is already set to the correct Bitbucket URL.
func (client *BitbucketClient) PushRepository(folderPath string) error {
	// Open the local repository.
	repo, err := git.PlainOpen(folderPath)
	if err != nil {
		return fmt.Errorf("failed to open repository at %s: %w", folderPath, err)
	}

	// Set up push options with HTTP basic authentication.
	pushOptions := &git.PushOptions{
		RemoteName: "origin",
		Auth: &githttp.BasicAuth{
			Username: client.Username,
			Password: client.AppPassword,
		},
	}

	// Push the repository to the remote.
	err = repo.Push(pushOptions)
	if err != nil {
		// If there's no new data to push, go-git returns git.NoErrAlreadyUpToDate.
		if err == git.NoErrAlreadyUpToDate {
			fmt.Println("Everything is already up-to-date.")
			return nil
		}
		return fmt.Errorf("failed to push repository: %w", err)
	}

	fmt.Println("Push successful!")
	return nil
}
