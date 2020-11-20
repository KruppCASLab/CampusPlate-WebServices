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
    "foodStopId": 1,
    "title": "Sandwiches",
    "description": "Cold cut and PB&J",
    "quantity": 10
}
```
* Response: 0 on success, failure otherwise

### Get Listings
* Description: Creates a listing. The userId will be from the user authenticated and the time will be from when the server receives the listing.
* Path: `https://<baseurl>/listings/`
* Method: GET
* Response: Array of listings within data field, status = 0 on success, failure otherwise
```json
{
    "data": [
        {
            "listingId": 143,
            "foodStopId": 3,
            "userId": 335,
            "title": "Cookies",
            "description": "Need a sweet treat, we got it!",
            "creationTime": 1605734297,
            "quantity": 30
        },
        {
            "listingId": 141,
// .. continue on
    ],
    "status": 0,
    "error": null
}

```

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

### Food Stops
### Create a Food Stop
* Description: Creates a food stop. This will be used mainly within the admin portal 
* Path: `https://<baseurl>/foodstops/`
* Method: POST
* Request:
```json
{
    "foodStopId": 1,
    "title": "Sandwiches",
    "description": "Cold cut and PB&J",
    "quantity": 10
}
```
* Response: 0 on success, failure otherwise

### Get Food Stops
Description: Gets list of food stops 
* Path: `https://<baseurl>/foodstops/`
* Method: GET
* Response: Array of food stops within data field, status = 0 on success, failure otherwise. 
```json
{
    "data": [
        {
            "foodStopId": 1,
            "name": "Knowlton Center",
            "description": "Macs",
            "lat": 41.374858,
            "lng": -81.851229
        },
        {
            "foodStopId": 4,
            "name": "The Union Dining Hall",
            "description": "See the buffet. Open 9am to 5pm from Monday through Friday.",
            "lat": 41.369176,
            "lng": -81.848572
        }
    ],
    "status": 0,
    "error": null
}
```
