package database

import (
	"database/sql"
	"fmt"

	_ "github.com/go-sql-driver/mysql"
)

// CreateDatabase connects to MySQL using admin credentials,
// checks if the database exists, creates it if it does not exist,
// creates a new user, and grants that user privileges on the new database.
//
// It returns a boolean indicating whether the database was newly created.
func CreateDatabase(databaseName, databaseUsername, databasePassword string) (bool, error) {
	// Admin credentials - update these with your actual admin username/password.
	adminDSN := "root:tyUy8O6msK@tcp(127.0.0.1:3306)/"

	// Open connection to MySQL as an admin.
	db, err := sql.Open("mysql", adminDSN)
	if err != nil {
		return false, fmt.Errorf("failed to open connection: %w", err)
	}
	defer db.Close()

	// Check if the database already exists.
	var dbCount int
	err = db.QueryRow(`
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.SCHEMATA 
        WHERE SCHEMA_NAME = ?
    `, databaseName).Scan(&dbCount)
	if err != nil {
		return false, fmt.Errorf("failed to check if database exists: %w", err)
	}

	dbCreated := false

	// Create the database only if it doesn't exist.
	if dbCount == 0 {
		createDBQuery := fmt.Sprintf("CREATE DATABASE `%s`", databaseName)
		if _, err = db.Exec(createDBQuery); err != nil {
			return false, fmt.Errorf("failed to create database: %w", err)
		}
		dbCreated = true
	}

	// Create the new user if it does not exist.
	createUserQuery := fmt.Sprintf("CREATE USER IF NOT EXISTS '%s'@'%%' IDENTIFIED BY '%s'", databaseUsername, databasePassword)
	if _, err = db.Exec(createUserQuery); err != nil {
		return dbCreated, fmt.Errorf("failed to create user: %w", err)
	}

	// Grant all privileges on the database to the new user.
	grantPrivilegesQuery := fmt.Sprintf("GRANT ALL PRIVILEGES ON `%s`.* TO '%s'@'%%'", databaseName, databaseUsername)
	if _, err = db.Exec(grantPrivilegesQuery); err != nil {
		return dbCreated, fmt.Errorf("failed to grant privileges: %w", err)
	}

	// Apply the privilege changes.
	if _, err = db.Exec("FLUSH PRIVILEGES"); err != nil {
		return dbCreated, fmt.Errorf("failed to flush privileges: %w", err)
	}

	// Return whether we created the database in this function call.
	return dbCreated, nil
}
