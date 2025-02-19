package apichecker

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/http/cookiejar"
)

type ApiChecker struct {
	Erp      string
	ApiUrl   string
	Username string
	Password string
	Database string
}

type Endpoint struct {
	Path  string
	Title string
}

var endpointsPriority = []Endpoint{
	{Path: "CUSTOMERS", Title: "לקוחות"},
	{Path: "LOGPART", Title: "פריטים"},
	{Path: "FAMILY_LOG", Title: "משפחות מוצר"},
	{Path: "ORDERS", Title: "הזמנות"},
	{Path: "AINVOICES", Title: "חשבוניות מס קבלה"},
	{Path: "CINVOICES", Title: "חשבוניות מרכזות"},
	{Path: "DOCUMENTS_N", Title: "החזרות"},
	{Path: "DOCUMENTS_D", Title: "?"},
	{Path: "CPROF", Title: "הצעות מחיר"},
	{Path: "PRICELIST", Title: "מחירונים"},
	{Path: "OBLIGO", Title: "כספים"},
	{Path: "AGENTS", Title: "סוכנים"},
	{Path: "ACCOUNTS_RECEIVABLE", Title: "גיול חובות"},
}

var endpointsSap = []Endpoint{
	{Path: "Items", Title: "פריטים"},
	{Path: "ItemGroups", Title: "משפחות מוצר"},
	{Path: "ItemProperties", Title: "פרמטרים"},
	{Path: "Orders", Title: "הזמנות"},
	{Path: "Invoices", Title: "חשבוניות"},
	{Path: "Returns", Title: "החזרות"},
	{Path: "Quotations", Title: "הצעות מחיר"},
	{Path: "DeliveryNotes", Title: "תעודות משלוח"},
	{Path: "JournalEntries", Title: "כרטסת"},
	{Path: "PriceLists", Title: "מחירונים"},
	{Path: "SpecialPrices", Title: "מחירים מיוחדים"},
	{Path: "Warehouses", Title: "מחסנים"},
	{Path: "SalesPersons", Title: "סוכנים"},
	{Path: "BusinessPartners", Title: "לקוחות"},
}

func Check(api *ApiChecker) {
	if api.Erp == "SAP" {
		sapCheck(api)
	} else if api.Erp == "PRIORITY" {
		priorityCheck(api)
	} else {
		fmt.Println("ERP not supported")
	}
}

func priorityCheck(api *ApiChecker) {
	client := &http.Client{
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	for _, endpoint := range endpointsPriority {
		url := fmt.Sprintf("%s/%s", api.ApiUrl, endpoint.Path)

		req, err := http.NewRequest("GET", url, nil)
		if err != nil {
			fmt.Printf("Error creating request for endpoint %s: %v\n", endpoint.Path, err)
			continue
		}

		req.SetBasicAuth(api.Username, api.Password)

		resp, err := client.Do(req)
		if err != nil {
			fmt.Printf("Error performing request for endpoint %s: %v\n", endpoint.Path, err)
			continue
		}
		resp.Body.Close()

		if resp.StatusCode == http.StatusOK {
			fmt.Printf("Endpoint %s (%s) is accessible (status 200)\n", endpoint.Path, endpoint.Title)
		} else {
			fmt.Printf("Endpoint %s (%s) returned status %d\n", endpoint.Path, endpoint.Title, resp.StatusCode)
		}
	}
}

func sapCheck(api *ApiChecker) {
	jar, err := cookiejar.New(nil)
	if err != nil {
		fmt.Println("Error creating cookie jar:", err)
		return
	}

	client := &http.Client{
		Jar: jar,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	loginUrl := fmt.Sprintf("%s/Login", api.ApiUrl)
	loginPayload := map[string]string{
		"CompanyDB": api.Database,
		"UserName":  api.Username,
		"Password":  api.Password,
	}

	jsonPayload, err := json.Marshal(loginPayload)
	if err != nil {
		fmt.Println("Error marshaling login payload:", err)
		return
	}

	req, err := http.NewRequest("POST", loginUrl, bytes.NewBuffer(jsonPayload))
	if err != nil {
		fmt.Println("Error creating login request:", err)
		return
	}
	req.Header.Set("Content-Type", "application/json")

	resp, err := client.Do(req)
	if err != nil {
		fmt.Println("Error during login request:", err)
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		fmt.Printf("Login failed with status: %d\n", resp.StatusCode)
		return
	}

	bodyBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		fmt.Println("Error reading login response:", err)
		return
	}

	var loginResponse struct {
		SessionId string `json:"SessionId"`
	}
	err = json.Unmarshal(bodyBytes, &loginResponse)
	if err != nil {
		fmt.Println("Error parsing login response:", err)
		return
	}

	if loginResponse.SessionId == "" {
		fmt.Println("No sessionId returned from login")
		return
	}

	fmt.Printf("Obtained session id: %s\n", loginResponse.SessionId)

	for _, endpoint := range endpointsSap {
		url := fmt.Sprintf("%s/%s", api.ApiUrl, endpoint.Path)

		req, err := http.NewRequest("GET", url, nil)
		if err != nil {
			fmt.Printf("Error creating request for endpoint %s: %v\n", endpoint.Path, err)
			continue
		}

		resp, err := client.Do(req)
		if err != nil {
			fmt.Printf("Error performing request for endpoint %s: %v\n", endpoint.Path, err)
			continue
		}
		resp.Body.Close()

		if resp.StatusCode == http.StatusOK {
			fmt.Printf("SAP Endpoint %s (%s) is accessible (status 200)\n", endpoint.Path, endpoint.Title)
		} else {
			fmt.Printf("SAP Endpoint %s (%s) returned status %d\n", endpoint.Path, endpoint.Title, resp.StatusCode)
		}
	}
}
