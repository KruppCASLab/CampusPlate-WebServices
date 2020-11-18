# CampusPlate - Web Service Catalog
## Introduction
The web services listed below are used for Campus Plate. Each response is encapsulated in a response object. If a web service does not specify a return object, it can be assumed that a generic one is used. Below is an example.

A status of 0 indicates success, any other number can mean an error or some other response. (-1 indicates that error was detected but not directly set in response). Data holds the corresponding data for that specific request. Error holds additional error information for the client. Below is an example:

``` java
{
    "data": null,
    "status": 0,
    "error": null
}
```

## Users
### User Registration
* Description: Register user using email address. By default, they are placed in a user role. If user account exists, account is replaced.
* Path: `https://<baseurl>/users`
* Method: POST
* Request:
``` json
{
"userName": "bkrupp@bw.edu"
}
```
* Response
	* 0 - Success
	* 1 - Failure on creation (Could be issue with service or DB)
	* 2 - Success - User existed but pin was updated

### User Confirmation
* Description: Confirm user account. Returns GUID to be stored on client that will be used later for authentication.
* Path: `https://<baseurl>/users/<username>`
* Method: PATCH
* Request:
```json
{
  "pin": 289759
}
```
* Response:
	* 0 - Success
	* 1 - Pin missing from request
	* 2 - Username and GUID combination does not exist
``` json
{
    "data": {
        "GUID": "e092ffab1cb9c01734c2b5f8c12aa432"
    },
    "status": 0,
    "error": null
}
```


## Listings
### Create a Listing
* Description: Creates a listing. The userId will be from the user authenticated and the time will be from when the server receives the listing.
* Path: `https://<baseurl>/listings/`
* Method: POST
* Request:
```json
{
    "title": "Cookies",
    "locationDescription": "Macs",
    "lat": 41.374414,
    "lng": -81.851910,
    "quantity": 20
}
```
* Response: 0 on success, failure otherwise

### Update a Listing
* Description: Creates a listing. The userId will be from the user authenticated and the time will be from when the server receives the listing.
* Path: `https://<baseurl>/listings/<listingid>`
* Method: PATCH
* Request: (Please note, the number is relative, so a positive number increase, a negative number decreases)
```json
{
  "quantity": -10
}
```
* Response: 0 on success, failure otherwise
