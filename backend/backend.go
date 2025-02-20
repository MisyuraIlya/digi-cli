package backend

import (
	"digi-cli/bitbucket"
	"fmt"
	"io"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strings"
	"time"

	"gopkg.in/src-d/go-git.v4"
	"gopkg.in/src-d/go-git.v4/plumbing/object"
)

type Backend struct {
	// Local AWS DB
	MysqlDbName     string
	MysqlDbPassword string

	// API
	Api      string 
	Erp      string 
	Username string 
	Password string
	Database string 

	// App Creation
	FolderName string 

	// Configuration
	Title           string
	IsWithMigvan    bool
	ObligoBlock     bool
	IsOnlinePrice   bool
	IsStockOnline   bool
	OneSignalAppId  string
	OneSignalKey    string
	SmsCenter       string
	SmsToken        string
	PaymentSystem   string
	SuccessLink     string
	ErrorLink       string
	Masof           string
	YadKey          string
	Passp           string
}


func CreateProject(project *Backend, bitbucketClient *bitbucket.BitbucketClient) {
	// 1. Clone template backend
	repoPath := "./" + project.FolderName
	err := bitbucketClient.DownloadRepository("digitradeteam", "backend-template", "main", repoPath)
	if err != nil {
		fmt.Println("Error cloning template repository:", err)
		return
	}
	fmt.Println("Template repository cloned successfully.")

	// 2. Remove .git folder
	gitPath := filepath.Join(repoPath, ".git")
	if err := os.RemoveAll(gitPath); err != nil {
		fmt.Println("Error deleting template .git folder:", err)
		return
	}
	fmt.Println("Template .git folder deleted successfully.")

	// 4. Update .env file
	if err := updateEnvFile(project); err != nil {
		fmt.Println("Error updating .env file:", err)
		return
	}

	// 5. Run Composer Install
	fmt.Println("Running composer install...")
	if err := RunCommand(repoPath, "composer", "install", "--no-interaction", "--optimize-autoloader"); err != nil {
		fmt.Println("Error running composer install:", err)
		return
	}

	// 5. Run Composer Install
	fmt.Println("composer dump-env dev")
	if err := RunCommand(repoPath, "composer", "dump-env", "dev"); err != nil {
		fmt.Println("Error running composer dump:", err)
		return
	}

	// 6. Execute Symfony commands
	fmt.Println("Running Symfony migrations...")
	if err := RunCommand(repoPath, "php", "bin/console", "make:migration"); err != nil {
		fmt.Println("Error running make:migration:", err)
		return
	}

	if err := RunCommand(repoPath, "php", "bin/console", "doctrine:migrations:migrate", "--no-interaction"); err != nil {
		fmt.Println("Error running doctrine:migrations:migrate:", err)
		return
	}

	fmt.Println("Migrations executed successfully.")

	// 5. Create (or reuse) a Bitbucket project
	_, err = bitbucketClient.CreateProjectOrUseExist("digitradeteam", project.FolderName, project.FolderName)
	if err != nil {
		fmt.Println("Error creating/retrieving project:", err)
	} else {
		fmt.Println("Project created/retrieved successfully")
	}

	// 6. Create a new repository under the Bitbucket project
	repoName := strings.ToLower(project.FolderName + "-backend")
	_, err = bitbucketClient.CreateRepository("digitradeteam", repoName, project.FolderName, true)
	if err != nil {
		fmt.Println("Error creating repository:", err)
		return
	}

	// Step 7: Obtain the new (empty) repository’s .git folder by "cloning" it into a temporary folder.
	// We pass an empty branch so that DownloadRepository initializes the repo locally.
	tempRepoPath := "./temp-" + project.FolderName
	err = bitbucketClient.DownloadRepository("digitradeteam", repoName, "", tempRepoPath)
	if err != nil {
		fmt.Println("Error setting up new Bitbucket repository:", err)
		return
	}
	fmt.Println("New Bitbucket repository initialized in temporary folder.")

	// Step 7: Copy the .git folder from the temporary repository to our project folder.
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

func updateEnvFile(project *Backend) error {
	// Path to the .env file in the project folder.
	envPath := filepath.Join("./", project.FolderName, ".env")
	
	// Read the file content.
	data, err := os.ReadFile(envPath)
	if err != nil {
		return fmt.Errorf("failed to read .env file: %v", err)
	}
	content := string(data)

	// --- Update Basic Configuration ---
	// TITLE
	titleRegex := regexp.MustCompile(`(?m)^TITLE=.*$`)
	content = titleRegex.ReplaceAllString(content, "TITLE="+project.Title)
	
	// --- Update Database URL ---
	newDatabaseURL := fmt.Sprintf("mysql://%s:%s@localhost:3306/%s?serverVersion=15&charset=utf8", project.MysqlDbName, project.MysqlDbPassword, project.MysqlDbName)
	dbRegex := regexp.MustCompile(`(?m)^DATABASE_URL=.*$`)
	content = dbRegex.ReplaceAllString(content, `DATABASE_URL="`+newDatabaseURL+`"`)
	
	// --- Update ERP CONFIG (Online and Cron services) ---
	erpTypeRegex := regexp.MustCompile(`(?m)^ERP_TYPE=.*$`)
	content = erpTypeRegex.ReplaceAllString(content, "ERP_TYPE="+project.Erp)
	
	erpUsernameRegex := regexp.MustCompile(`(?m)^ERP_USERNAME=.*$`)
	content = erpUsernameRegex.ReplaceAllString(content, "ERP_USERNAME="+project.Username)
	
	erpPasswordRegex := regexp.MustCompile(`(?m)^ERP_PASSWORD=.*$`)
	content = erpPasswordRegex.ReplaceAllString(content, "ERP_PASSWORD="+project.Password)
	
	erpUrlRegex := regexp.MustCompile(`(?m)^ERP_URL=.*$`)
	content = erpUrlRegex.ReplaceAllString(content, "ERP_URL="+project.Api)
	
	erpDbRegex := regexp.MustCompile(`(?m)^ERP_DB=.*$`)
	content = erpDbRegex.ReplaceAllString(content, "ERP_DB="+project.Database)
	
	// --- Update CONFIGURATION ---
	// IS_WITH_MIGVAN
	isWithMigvanRegex := regexp.MustCompile(`(?m)^IS_WITH_MIGVAN=.*$`)
	content = isWithMigvanRegex.ReplaceAllString(content, fmt.Sprintf("IS_WITH_MIGVAN=%t", project.IsWithMigvan))
	
	// OBLIGO_BLOCK
	obligoBlockRegex := regexp.MustCompile(`(?m)^OBLIGO_BLOCK=.*$`)
	content = obligoBlockRegex.ReplaceAllString(content, fmt.Sprintf("OBLIGO_BLOCK=%t", project.ObligoBlock))
	
	// IS_ONLINE_PRICE
	isOnlinePriceRegex := regexp.MustCompile(`(?m)^IS_ONLINE_PRICE=.*$`)
	content = isOnlinePriceRegex.ReplaceAllString(content, fmt.Sprintf("IS_ONLINE_PRICE=%t", project.IsOnlinePrice))
	
	// IS_STOCK_ONLINE
	isStockOnlineRegex := regexp.MustCompile(`(?m)^IS_STOCK_ONLINE=.*$`)
	content = isStockOnlineRegex.ReplaceAllString(content, fmt.Sprintf("IS_STOCK_ONLINE=%t", project.IsStockOnline))
	
	// --- Update INTEGRATION ---
	oneSignalAppIdRegex := regexp.MustCompile(`(?m)^ONE_SIGNAL_APP_ID=.*$`)
	content = oneSignalAppIdRegex.ReplaceAllString(content, "ONE_SIGNAL_APP_ID="+project.OneSignalAppId)
	
	oneSignalKeyRegex := regexp.MustCompile(`(?m)^ONE_SIGNAL_KEY=.*$`)
	content = oneSignalKeyRegex.ReplaceAllString(content, "ONE_SIGNAL_KEY="+project.OneSignalKey)
	
	smsCenterRegex := regexp.MustCompile(`(?m)^SMS_CENTER=.*$`)
	content = smsCenterRegex.ReplaceAllString(content, "SMS_CENTER="+project.SmsCenter)
	
	smsTokenRegex := regexp.MustCompile(`(?m)^SMS_TOKEN=.*$`)
	content = smsTokenRegex.ReplaceAllString(content, "SMS_TOKEN="+project.SmsToken)
	
	paymentSystemRegex := regexp.MustCompile(`(?m)^PAYMENT_SYSTEM=.*$`)
	content = paymentSystemRegex.ReplaceAllString(content, "PAYMENT_SYSTEM="+project.PaymentSystem)
	
	successLinkRegex := regexp.MustCompile(`(?m)^SUCCESS_LINK=.*$`)
	content = successLinkRegex.ReplaceAllString(content, "SUCCESS_LINK="+project.SuccessLink)
	
	errorLinkRegex := regexp.MustCompile(`(?m)^ERROR_LINK=.*$`)
	content = errorLinkRegex.ReplaceAllString(content, "ERROR_LINK="+project.ErrorLink)
	
	masofRegex := regexp.MustCompile(`(?m)^MASOF=.*$`)
	content = masofRegex.ReplaceAllString(content, "MASOF="+project.Masof)
	
	yadKeyRegex := regexp.MustCompile(`(?m)^YAD_KEY=.*$`)
	content = yadKeyRegex.ReplaceAllString(content, "YAD_KEY="+project.YadKey)
	
	passpRegex := regexp.MustCompile(`(?m)^PASSP=.*$`)
	content = passpRegex.ReplaceAllString(content, "PASSP="+project.Passp)

	// Write the updated content back to the .env file.
	if err := os.WriteFile(envPath, []byte(content), 0644); err != nil {
		return fmt.Errorf("failed to write updated .env file: %v", err)
	}
	fmt.Println(".env file updated successfully.")
	return nil
}

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

func RunCommand(dir string, name string, args ...string) error {
	cmd := exec.Command(name, args...)
	cmd.Dir = dir
	cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr

	err := cmd.Run()
	if err != nil {
		return fmt.Errorf("error executing %s: %v", name, err)
	}
	return nil
}
