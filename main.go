package main

import (
	"fmt"
	"strconv"
	"strings"
	"digi-cli/bitbucket"
	"digi-cli/frontend"
)


var menu = map[string]func(*bitbucket.BitbucketClient){
    "1": createProject,
}

var menuVariatns = []string{
	"1. Create new project",
	"2. Exit",
	"Choose variant",
}

func main() {
	client := &bitbucket.BitbucketClient{
		Username:    "ilya_mi",
		AppPassword: "3FXHJRwT4pZ28xY2PULh",
	}

	fmt.Println("Digitrage Ilya manager")

Menu:
	for {
		variant := promptData(menuVariatns...)
		menuFunc := menu[variant]
		if menuFunc == nil {
			break Menu
		}
		menuFunc(client)
	}
}

func createProject(clientBitbucket *bitbucket.BitbucketClient) {
	erpOptions := []string{
		"SAP",
		"PRIORITY",
	}

	// paymenyOptions := []string{
	// 	"yadsarig",
	// 	"tranzilla",
	// 	"none",
	// }


	erp := promptSelectData("Which ERP is used by the client?", erpOptions)
	projectNameEnglish := promptData("write the project name in english (camel case like mrKelim)")
	projectNameHebrew := promptData("write the project name in hebrew")
	// projectDescription := promptData("write the short desctiption in hebrew")
	// minimumPriceStr := promptData("Write the minimum price for order")
	// deliveryPriceStr := promptData("Write delivery price for order")
	// isWithStock := promptBool("is used stock?")
	// isOpenWorld := promptBool("is open world the app? (else stricted for client only)")
	// email := promptData("write the email for support footer")
	// location := promptData("write the location for support footer")
	// phoneSupport := promptData("write the phone support for support footer")
	// fax := promptData("write the fax for support footer")
	// footerDescription1 := promptData("write section 1 description footer")
	// footerDescription2 := promptData("write section 2 description footer")
	// footerDescription3 := promptData("write section 3 description footer")
	// primaryColor := promptData("write primary color of the app")
	// secondaryColor := promptData("write secondary color of the app")
	// oneSignalKey := promptData("set the one signal key")
	// paymentSystem := promptData("set the one signal key")
	// minimumPrice, err := strconv.ParseFloat(minimumPriceStr, 64)
	// if err != nil {
	// 	fmt.Println("Invalid minimum price:", err)
	// 	return
	// }

	// deliveryPrice, err := strconv.ParseFloat(deliveryPriceStr, 64)
	// if err != nil {
	// 	fmt.Println("Invalid delivery price:", err)
	// 	return
	// }

	project := &frontend.Frontend{
		FolderName: 		projectNameEnglish,
		Erp:				erp,
		Title:              projectNameHebrew,
		// Description:        projectDescription,
		// MinimumPrice:       minimumPrice,
		// DeliveryPrice:      deliveryPrice,
		// IsWithStock:        isWithStock,
		// IsOpenWorld:        isOpenWorld,
		// Email:              email,
		// Location:           location,
		// PhoneSupport:       phoneSupport,
		// Fax:                fax,
		// FooterDescription1: footerDescription1,
		// FooterDescription2: footerDescription2,
		// FooterDescription3: footerDescription3,
		// PrimaryColor:       primaryColor,
		// SecondaryColor:     secondaryColor,
		// OneSignalKey:       oneSignalKey,
		// PaymentSystem:      paymentSystem,
	}
	frontend.CreateProject(project,clientBitbucket)
}

func promptData(prompt ...string) string {
	for i, line := range prompt {
		if i == len(prompt)-1 {
			fmt.Printf("%v: ", line)
		} else {
			fmt.Println(line)
		}
	}
	var res string
	fmt.Scanln(&res)
	return res
}

// promptSelectData displays a list of options and asks the user to choose one.
func promptSelectData(question string, options []string) string {
	fmt.Println(question)
	// Display options with their numbers.
	for i, option := range options {
		fmt.Printf("%d. %s\n", i+1, option)
	}
	fmt.Print("Choose an option (enter the number): ")

	var input string
	fmt.Scanln(&input)

	// Convert input to an integer index.
	index, err := strconv.Atoi(input)
	if err != nil || index < 1 || index > len(options) {
		fmt.Println("Invalid selection, please try again.")
		// Recursively ask until a valid option is chosen.
		return promptSelectData(question, options)
	}
	return options[index-1]
}

func promptBool(prompt string) bool {
	for {
		// Display the prompt with a (y/n) hint.
		fmt.Printf("%s (y/n): ", prompt)
		var input string
		fmt.Scanln(&input)

		// Normalize the input to lower case and trim any extra spaces.
		input = strings.TrimSpace(strings.ToLower(input))
		if input == "y" || input == "yes" {
			return true
		} else if input == "n" || input == "no" {
			return false
		}
		fmt.Println("Invalid input. Please enter 'y' for yes or 'n' for no.")
	}
}
