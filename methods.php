<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return json_decode('
{
    "_default": [
		"Standard",
		"Express"
    ],
	"bigcommerce": [
		"Standard",
		"Express"
	],
	"bluefly": [
		"Standard",
		"Expedited"
	],
	"custom": [
		"Express",
		"NextDay",
		"SecondDay",
		"Scheduled (freight)",
		"Standard"
	],
	"ebay": [
		"Standard",
		"Express"
	],
	"etsy": [
		"Standard",
		"Express"
	],
	"fba": [
		"Standard",
		"Express",
		"SecondDay",
		"NextDay",
		"Expedited"
	],
	"fbm": [
		"Standard",
		"Express",
		"Std US D2D Dom",
		"SecondDay",
		"Econ US Dom",
		"Exp US D2D Dom",
		"Second US D2D Dom",
		"Std US D2D Intl",
		"Std CA D2D Dom",
		"Next US D2D Dom",
		"Exp US D2D Intl"
	],
	"googleexpress": [
		"Standard",
		"Ground",
		"Free Shipping",
		"Standard Shipping",
		"LSE Lighting SHIPPING",
		"Free Standard",
		"Expedited",
		"USPS",
		"Fedex",
		"Shipping",
		"ships",
		"Standard USPS",
		"Shopping Actions",
		"USPS - Unites States Postal Service",
		"Priority Delivery",
		"US - Free Shipping - 2-7 Day",
		"Standard Ground",
		"USPS Priority Mail",
		"USPS First Class",
		"FedEx Home Delivery",
		"Ground Shipping"
	],
	"groupongoods": [
		"BEST"
	],
	"houzz": [
		"Standard",
		"Expedited"
	],
	"opencart": [
		"Priority Mail 2-Day",
		"Priority Mail 1-Day",
		"UPS Ground",
		"First-Class Package Service - Retail",
		"Priority Mail 3-Day",
		"First-Class Package International Service (Estimat",
		"Priority Mail Express 2-Day",
		"Standard",
		"SecondDay"
	],
	"overstock": [
		"Standard",
		"Express",
		"GROUND",
		"TWO_DAY",
		"NEXT_DAY",
		"THREE_DAY"
	],
	"shopify": [
		"A1",
		"A&MTrucking",
		"ABF",
		"ADuiePyle",
		"APEX",
		"Averitt",
		"CEVA",
		"DHLEasyReturnGround",
		"DHLEasyReturnLight",
		"DHLEasyReturnPlus",
		"DHLeCommerce",
		"DHLExpress12",
		"DHLExpress9",
		"DHLExpressEnvelope",
		"DHLExpressWorldwide",
		"DHLSmartmailFlatsExpedited",
		"DHLSmartmailFlatsGround",
		"DHLSmartmailParcelExpedited",
		"DHLSmartmailParcelGround",
		"DHLSmartmailParcelPlusExpedited",
		"DHLSmartmailParcelPlusGround",
		"DynamexSameDay",
		"EasternConnectionExpeditedMail",
		"EasternConnectionGround",
		"EasternConnectionPriority",
		"EasternConnectionSameDay",
		"EnsendaHome",
		"EnsendaNextDay",
		"EnsendaSameDay",
		"EnsendaTwoMan",
		"Estes",
		"Fedex2Day",
		"FedExExpeditedFreight",
		"FedexExpressSaver",
		"FedexFirstOvernight",
		"FedexFreight",
		"FedExGround",
		"FedExHome",
		"FedexPriorityOvernight",
		"FedexSameDay",
		"FedExSmartPost",
		"FedExSmartPostReturns",
		"FedexStandardOvernight",
		"GSOFreight",
		"GSOGround",
		"GSOPriority",
		"LandAirExpress",
		"LaserShipGlobalPriority",
		"LaserShipNextDay",
		"LasershipSameDay",
		"LSO2ndDay",
		"LSOEarlyNexyDay",
		"LSOEconomyNextDay",
		"LSOGround",
		"LSOPriorityNextDay",
		"LSOSa"
	],
	"walmart": [
		"Standard",
		"Express",
		"OneDay",
		"Freight",
		"WhiteGlove",
		"Value",
		"Ground Home Delivery"
	],
	"walmartca": [
		"Standard",
		"Express",
		"Freight",
		"OneDay",
		"WhiteGlove",
		"Value",
		"Ground Home Delivery"
	],
	"walmartdsv": [
		"Standard",
		"OneDay",
		"Express",
		"Freight",
		"Value",
		"WhiteGlove",
		"Ground Home Delivery"
	],
	"wayfair": [
		"GR",
		"ND",
		"2D",
		"3D",
		"TRB",
		"WGB",
		"WGS",
		"WGG",
		"WGP",
		"TFLG",
		"ND9",
		"ND12",
		"SAT",
		"WGM",
		"FSP",
		"LADROC",
		"LADRIO",
		"LADRIH",
		"LADRHO",
		"TP",
		"LADRMO",
		"LADRIM",
		"FDHD",
		"LC",
		"Ground Home Delivery",
		"Home Delivery",
		"Ground"
	],
	"woocommerce": [
		"Standard",
		"Express"
	]
}
', true);