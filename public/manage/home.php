<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- development version, includes helpful console warnings -->
    <script src="https://unpkg.com/vue@next"></script>
    <script src="js/main.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/main.css">
    <title>CampusPlate | Manage</title>
</head>
<body>
<div class="container min-vh-100 h-100" id="login">
    <h1>Campus Plate | Home </h1>

</div>
<script>
    const Login = {
        data() {
            return {
                username: "",
                password: ""
            }
        },
        methods: {
            login() {
                let data = {
                    "username": this.username,
                    "password": this.password
                };
                fetch(serviceEndpoint + "auth",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data),
                    }).then((response) => {
                    if (response.status === 401) {
                        document.getElementById("invalidAlert").style.display = "block";
                    } else {
                        document.getElementById("invalidAlert").style.display = "none";
                        window.location.replace("home.php");
                    }
                }).catch((error) => {
                    console.log("Received an error" + error);
                });
            }
        }
    }
    Vue.createApp(Login).mount('#login');
</script>
</body>
</html>
