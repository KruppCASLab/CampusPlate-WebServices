# CampusPlate - Web Services
## Campus Plate - Migration Steps

To use the newest version of Campus Plate web services, there are several small changes to the database and new services that need to be installed

### Steps
Below are the steps to be performed:
1. Stop the web server so people cannot access Campus Plate (Ideally, this is not during a time when it is being used)
2. Make the database change described below
   * On the table `tblFoodStops`, perform the following:
     * Delete column `type`
     * Add the following columns (BOOLEAN)
   ```sql
     `managed` tinyint(1) NOT NULL,
     `reservable` tinyint(1) NOT NULL,
   ```
3. Install the latest web services using rsync
4. Add to `config.cfg` the `organization` entry in the `app` stanza:

<<<<<<< Updated upstream
=======
As a **food stop manager**, you will primarily interact with the app on the *food stop management dashboard*, which is located *online*, not in-app. we recommend a desktop or tablet interface for the website.
# [1] Registering your account
All managers and Admins first need to register for an account.
1. Go to https://caslab.case.edu/cp/passreset.php. ![Pasted image 20251112111834.png](img/Pasted%20image%2020251112111834.png)
2. Enter in your email address and ``Continue``.
3. Check your Institute email for a verification code: ![Pasted image 20251112112202.png](img/Pasted%20image%2020251112112202.png)
4. Enter the verification code and construct a password. ![Pasted image 20251112112254.png](img/Pasted%20image%2020251112112254.png)
   Congrats! You are now registered in the database as a user! However, when you try logging in for the first time, you will likely see the message:
>>>>>>> Stashed changes
```
[app]
enable_test_user = false
image_dir = "/var/www/cp/images"
organization = "CWRU"

```
<<<<<<< Updated upstream
   
6. Verify configuration file
7. Restart web server
8. Verify access via mobile application and access via administrative dashboard 

---

## Web Service Catalog
The web services contain both the service endpoint (public/rest.php) and the web portal used for administrators (public/index.php).

The web services listed below are used for Campus Plate. Each response is encapsulated in a response object. If a web service does not specify a return object, it can be assumed that a generic one is used. Below is an example.

A status of 0 indicates success, any other number can mean an error or some other response. (-1 indicates that error was detected but not directly set in response). Data holds the corresponding data for that specific request. Error holds additional error information for the client. Below is an example:

``` json
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
	* When creating a listing, the default post date is the current date and the default expiration date is a set amount past that.
* Path: `https://<baseurl>/listings/`
* Method: POST
* Request:
	* Please note: the image property is optional. If submitting an image with the listing, the image data should be base64_encoded.
```json
{
    "foodStopId": 1,
    "title": "Sandwiches",
    "description": "Cold cut and PB&J",
    "quantity": 10,
	  "weightOunces": 12,
    "creationDate": 1616526584, // Timestamp of date
    "expirationDate": 1616528584, // Timestamp of expiration
    "image": "REPLACE_WITH_BASE_64_ENCODING_OF_IMAGE"
}
```
* Response: 0 on success, failure otherwise

### Get Listings
* Description: Returns a list of listings for all food stops. The userId will be from the user authenticated and the time will be from when the server receives the listing. **Please note: Images are not returned with getListings, a different service is used for the image (see below)**
	* It only returns listings that have not expired yet
	  **Please note: Listings also return the quantity remaining. This is based off of what has been reserved or fulfilled.**
* Path: `https://<baseurl>/listings/` (Gets all listings)
	* Path: `https://<baseurl>/listings/1/foodstop` (Returns listings from a specific food stop, in this case, food stop with id 1)
* Method: GET
* Response: Array of listings within data field, status = 0 on success, failure otherwise
```json

    "data": [
        {
            "listingId": 214,
            "foodStopId": 1,
            "userId": 339,
            "title": "Cheese Puffs",
            "description": "Dan's Favourite",
            "creationTime": 1616526180,
            "expirationTime": 1616698920,
            "quantity": 24,
            "weightOunces": 13,
            "image": null,
            "quantityRemaining": 24
        },
        {
            "listingId": 213,
            "foodStopId": 1,
            "userId": 339,
            "title": "Sandwiches",
            "description": "Cold cut and PB&J",
            "creationTime": 1616525969,
            "expirationTime": 1616698769,
            "quantity": 10,
            "weightOunces": 12,
            "image": null,
            "quantityRemaining": 10
        }
	  ],
    "status": 0,
    "error": null
}
```

### Get Listing Image
* Description: Gets an image from a particular listing
* Path: `https://<baseurl>/listings/<listingid>/image`
* Method: GET
* Response: Returns the image data, base64 encoded.

``` json
{
    "data": "/9j/4AAQS ...",
    "status": 0,
    "error": null
}
```

## Food Stops
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
            "streetAddress": "456 Terrell Rd",
            "lat": 41.374858,
            "lng": -81.851229,
            "hexColor": "FF9999",
            "foodStopNumber": 1
        },
        {
            "foodStopId": 2,
            "name": "Veterans Center",
            "description": "See Randy Stevenson",
            "streetAddress": "123 Dyland Rd",
            "lat": 41.369901,
            "lng": -81.849166,
            "hexColor": "99FF99",
            "foodStopNumber": 2
        }
    ],
    "status": 0,
    "error": null
}

```

### Get Food Stops Managed By User
Description: Gets list of food stops that the user who is authenticating manages
* Path: `https://<baseurl>/foodstops/manage`
* Method: GET
* Response: Array of food stops within data field, status = 0 on success, failure otherwise.  If person does not manage food stops, data will be an empty array
```json
{
    "data": [
        {
            "foodStopId": 1,
            "name": "Knowlton Center",
            "description": "Macs",
            "streetAddress": "456 Terrell Rd",
            "lat": 41.374858,
            "lng": -81.851229,
            "hexColor": "FF9999",
            "foodStopNumber": 1
        },
        {
            "foodStopId": 2,
            "name": "Veterans Center",
            "description": "See Randy Stevenson",
            "streetAddress": "123 Dyland Rd",
            "lat": 41.369901,
            "lng": -81.849166,
            "hexColor": "99FF99",
            "foodStopNumber": 2
        }
    ],
    "status": 0,
    "error": null
}
```

## Reservations
### Create a Reservation
Description: User attempts to create a reservation.
* Path: `https://<baseurl>/reservations`
* Method: POST
* Request: listingId is what the user is trying to reserve, quantity is the amount
```json
{
    "listingId" : 166,
    "quantity" : 1
}
```
* Response:  data is the reservation. code is the unique code that should be shown to the food stop manager. timeExpired is when their reservation expires. quantity is how much they reserved.
	* **Please note:** status = 0 on success, 1 on quantity not available, 2 on listings no longer available
``` json
{
    "data": {
        "reservationId": null,
        "userId": 339,
        "listingId": 166,
        "quantity": 1,
        "status": 0,
        "code": 764888,
        "timeCreated": 1614371897,
        "timeExpired": 1614373697
    },
    "status": 0,
    "error": null
}
```

### Get Reservations
Description: Gets reservations from users that have not expired OR that have not been fulfilled
* Path: `https://<baseurl>/reservations`
* Method: GET
``` json
{
    "data": [
        {
            "reservationId": 7,
            "userId": 339,
            "listingId": 167,
            "quantity": 1,
            "status": 0,
            "code": 533339,
            "timeCreated": 1614370028,
            "timeExpired": 1614371828
        },
        {
            "reservationId": 8,
            "userId": 339,
            "listingId": 167,
            "quantity": 1,
            "status": 0,
            "code": 423653,
            "timeCreated": 1614370035,
            "timeExpired": 1614371835
        }
    ],
    "status": 0,
    "error": null
}
```
=======
This means you require the admin, Allison, to make you a food stop manager.
# Changing User permissions (Admin only)
As an admin, when you log in, you will see a dashboard that looks something like this.![Pasted image 20251112115621.png](img/Pasted%20image%2020251112115621.png)
Notice the admin tab?
1. Navigate to the admin tab.
> TODO: finish this section

Now the User should be able to log in! Have them attempt it to be sure. They should now see this dashboard:
![Pasted image 20251112115621.png](img/Pasted%20image%2020251112115621.png)

# [2] Managing listings at your food stop
Now that you are a **foodstop manager**, your main usage of this webportal will be adding, updating, and deleting listings.
> A listing refers to any offering that your stop may have.
## 1. Adding a listing 

## 2. Updating a listing
## 3. Deleting a listing 
# [3] Orders
As a manager of unmannaged foodstops, you can add orders of incoming people through the portal.
>>>>>>> Stashed changes
