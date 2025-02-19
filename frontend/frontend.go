package frontend

import (
	"digi-cli/bitbucket"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"
	"time"

	git "gopkg.in/src-d/go-git.v4"
	"gopkg.in/src-d/go-git.v4/plumbing/object"
)

type Frontend struct {
	FolderName string `json:"folderName"`
	Erp        string `json:"Erp"`
	Title      string `json:"title"`
	// Other fields ...
}

func CreateProject(project *Frontend, bitbucketClient *bitbucket.BitbucketClient) {
	// Step 1: Clone the template repository into the project folder.
	repoPath := "./" + project.FolderName
	err := bitbucketClient.DownloadRepository("digitradeteam", "frontend-template", "main", repoPath)
	if err != nil {
		fmt.Println("Error cloning template repository:", err)
		return
	}
	fmt.Println("Template repository cloned successfully.")

	// Step 2: Remove the template's .git folder so that we can attach a new one.
	gitPath := filepath.Join(repoPath, ".git")
	if err := os.RemoveAll(gitPath); err != nil {
		fmt.Println("Error deleting template .git folder:", err)
		return
	}
	fmt.Println("Template .git folder deleted successfully.")

	// (Optional) Step 3: Create/update any configuration files in the project.
	configContent := fmt.Sprintf(`global.settings = {
		title: "%s",
		Erp: "%s"
	}`, project.Title, project.Erp)
	configPath := filepath.Join(repoPath, "global.js")
	if err := os.WriteFile(configPath, []byte(configContent), 0644); err != nil {
		fmt.Println("Error writing global.js file:", err)
		return
	}
	fmt.Println("global.js file created successfully.")

	// 4. Create (or reuse) a Bitbucket project
	_, err = bitbucketClient.CreateProjectOrUseExist("digitradeteam", project.FolderName, project.FolderName)
	if err != nil {
		fmt.Println("Error creating/retrieving project:", err)
	} else {
		fmt.Println("Project created/retrieved successfully")
	}

	// 5. Create a new repository under the Bitbucket project
	repoName := strings.ToLower(project.FolderName + "-frontend")
	_, err = bitbucketClient.CreateRepository("digitradeteam", repoName, project.FolderName, true)
	if err != nil {
		fmt.Println("Error creating repository:", err)
		return
	}

	// Step 5: Obtain the new (empty) repository’s .git folder by "cloning" it into a temporary folder.
	// We pass an empty branch so that DownloadRepository initializes the repo locally.
	tempRepoPath := "./temp-" + project.FolderName
	err = bitbucketClient.DownloadRepository("digitradeteam", repoName, "", tempRepoPath)
	if err != nil {
		fmt.Println("Error setting up new Bitbucket repository:", err)
		return
	}
	fmt.Println("New Bitbucket repository initialized in temporary folder.")

	// Step 6: Copy the .git folder from the temporary repository to our project folder.
	srcGitPath := filepath.Join(tempRepoPath, ".git")
	destGitPath := filepath.Join(repoPath, ".git")
	err = copyDir(srcGitPath, destGitPath)
	if err != nil {
		fmt.Println("Error copying .git folder:", err)
		return
	}
	fmt.Println(".git folder copied successfully to project folder.")

	// Step 7: Remove the temporary repository.
	err = os.RemoveAll(tempRepoPath)
	if err != nil {
		fmt.Println("Error removing temporary repository:", err)
		return
	}
	fmt.Println("Temporary repository removed.")

	// Step 7: Open the repository in the project folder, add files, commit, and push.
	repo, err := git.PlainOpen(repoPath)
	if err != nil {
		fmt.Println("Error opening project repository:", err)
		return
	}

	// Get the working tree.
	wt, err := repo.Worktree()
	if err != nil {
		fmt.Println("Error accessing worktree:", err)
		return
	}

	// Stage all files.
	err = wt.AddGlob(".")
	if err != nil {
		fmt.Println("Error adding files to staging area:", err)
		return
	}
	// Commit the changes.
	commitHash, err := wt.Commit("Initial commit", &git.CommitOptions{
		Author: &object.Signature{
			Name:  "Ilya Misyura",
			Email: "ilya.mi@digi-trade.io",
			When:  time.Now(),
		},
	})
	if err != nil {
		fmt.Println("Error committing changes:", err)
		return
	}
	fmt.Println("Commit created with hash:", commitHash)

	// Step 8: Push the commit to Bitbucket.
	err = bitbucketClient.PushRepository(repoPath)
	if err != nil {
		fmt.Println("Error pushing repository:", err)
		return
	}
	fmt.Println("Repository pushed to Bitbucket successfully!")
}

// copyDir recursively copies a directory from src to dst.
func copyDir(src string, dst string) error {
	return filepath.Walk(src, func(path string, info os.FileInfo, err error) error {
		if err != nil {
			return err
		}
		// Determine the path relative to the source.
		relPath, err := filepath.Rel(src, path)
		if err != nil {
			return err
		}
		destPath := filepath.Join(dst, relPath)
		if info.IsDir() {
			// Create the directory with the same permissions.
			return os.MkdirAll(destPath, info.Mode())
		}

		// For files, open the source file.
		srcFile, err := os.Open(path)
		if err != nil {
			return err
		}
		defer srcFile.Close()

		// Create the destination file.
		destFile, err := os.OpenFile(destPath, os.O_CREATE|os.O_WRONLY, info.Mode())
		if err != nil {
			return err
		}
		defer destFile.Close()

		// Copy the file content.
		_, err = io.Copy(destFile, srcFile)
		return err
	})
}
