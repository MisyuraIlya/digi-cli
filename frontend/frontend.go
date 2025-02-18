package frontend

type Frontend struct {
	Title              string  `json:"title"`
	Description        string  `json:"description"`
	MinimumPrice       float64 `json:"minimumPrice"`
	DeliveryPrice      float64 `json:"deliveryPrice"`
	IsWithStock        bool    `json:"isWithStock"`
	IsOpenWorld        bool    `json:"isOpenWorld"`
	Email              string  `json:"email"`
	Location           string  `json:"location"`
	PhoneSupport       string  `json:"phoneSupport"`
	Fax                string  `json:"fax"`
	FooterDescription1 string  `json:"footerDescription1"`
	FooterDescription2 string  `json:"footerDescription2"`
	FooterDescription3 string  `json:"footerDescription3"`
	PrimaryColor       string  `json:"primaryColor"`
	SecondaryColor     string  `json:"secondaryColor"`
	OneSignalKey       string  `json:"oneSignalKey"`
	PaymentSystem      string  `json:"paymentSystem"`
}

func CreateProject(project *Frontend) {

}