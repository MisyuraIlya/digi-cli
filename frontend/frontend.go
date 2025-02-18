package frontend

import (
	"digi-cli/bitbucket"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"
)

type Frontend struct {
	FolderName string `json:"folderName"`
	Erp        string `json:"Erp"`
	Title      string `json:"title"`
	// Other fields ...
}

func CreateProject(project *Frontend, bitbucketClient *bitbucket.BitbucketClient) {
	// 1. Clone the template repository into a new folder
	repoPath := "./" + project.FolderName
	err := bitbucketClient.DownloadRepository("digitradeteam", "frontend-template", "main", repoPath)
	if err != nil {
		fmt.Println("Error cloning repository:", err)
		return
	}

	// 2. Delete the template's .git folder so we can later attach the new repository's .git
	gitPath := filepath.Join(repoPath, ".git")
	if err := os.RemoveAll(gitPath); err != nil {
		fmt.Println("Error deleting .git folder:", err)
		return
	}
	fmt.Println(".git folder deleted successfully.")

	// 3. Create a new configuration file with project parameters
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
	bbProject, err := bitbucketClient.CreateProjectOrUseExist("digitradeteam", project.FolderName, project.FolderName)
	if err != nil {
		fmt.Println("Error creating/retrieving project:", err)
	} else {
		fmt.Printf("Project details: %+v\n", bbProject)
	}

	// 5. Create a new repository under the Bitbucket project
	repoName := strings.ToLower(project.FolderName + "-frontend")
	repo, err := bitbucketClient.CreateRepository("digitradeteam", repoName, project.FolderName, true)
	if err != nil {
		fmt.Println("Error creating repository:", err)
		return
	} else {
		fmt.Printf("Repository details: %+v\n", repo)
	}

	// 6. Clone the newly created repository to obtain its .git folder.
	// We clone into a temporary folder.
	tempRepoPath := "./temp-" + repoName
	err = bitbucketClient.DownloadRepository("digitradeteam", repoName, "main", tempRepoPath)
	if err != nil {
		fmt.Println("Error cloning newly created repository:", err)
		return
	}

	// 7. Copy the .git folder from the temporary clone into our project folder.
	srcGitPath := filepath.Join(tempRepoPath, ".git")
	destGitPath := filepath.Join(repoPath, ".git")
	err = copyDir(srcGitPath, destGitPath)
	if err != nil {
		fmt.Println("Error copying .git folder:", err)
		return
	}

	// 8. Remove the temporary repository clone.
	err = os.RemoveAll(tempRepoPath)
	if err != nil {
		fmt.Println("Error removing temporary repository:", err)
		return
	}

	// 9. Push the repository to Bitbucket using the new .git folder.
	err = bitbucketClient.PushRepository(repoPath)
	if err != nil {
		fmt.Printf("Error pushing repository: %v\n", err)
		return
	}

	fmt.Println("Operations completed successfully!")
}

// copyDir recursively copies a directory from src to dst.
func copyDir(src string, dst string) error {
	// Walk through the source directory.
	return filepath.Walk(src, func(path string, info os.FileInfo, err error) error {
		if err != nil {
			return err
		}
		// Determine the relative path.
		relPath, err := filepath.Rel(src, path)
		if err != nil {
			return err
		}
		destPath := filepath.Join(dst, relPath)
		if info.IsDir() {
			// Create directory with the same permissions.
			return os.MkdirAll(destPath, info.Mode())
		}

		// For files, open source file.
		srcFile, err := os.Open(path)
		if err != nil {
			return err
		}
		defer srcFile.Close()

		// Create destination file.
		destFile, err := os.OpenFile(destPath, os.O_CREATE|os.O_WRONLY, info.Mode())
		if err != nil {
			return err
		}
		defer destFile.Close()

		// Copy file content.
		_, err = io.Copy(destFile, srcFile)
		return err
	})
}
