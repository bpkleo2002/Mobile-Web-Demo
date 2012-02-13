<!DOCTYPE html>
<?php
session_start();
?>
<html>
    <head>
        <title>MobileAppLab Data Service</title>
        <meta name="viewport" content="user-scalable=yes, width=device-width" />
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.0.1/jquery.mobile-1.0.1.min.css" />
        <script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.0.1/jquery.mobile-1.0.1.min.js"></script>
        <style>
            p.padded {
                padding: 10px;
            }
        </style>
    </head>
    <body>
        <div data-role="page" id="Start">
            <div data-role="header"><h1>MobileAppLab Data Service</h1></div>
            <div data-role="content">
                <p>Welcome to the MobileAppLab Data Service. This serviced can be used by other web applications to store data securely in the cloud.</p>
                <ul data-role="listview" data-inset="true" id="CommandList">
                </ul>
                <p id="DataListMessage"></p>
                <ul data-role="listview" data-inset="true" id="DataList">
                </ul>
            </div>
        </div>
        <div data-role="page" id="Register">
            <div data-role="header">
                <h1>Register</h1>
                <a data-rel="back" data-icon="arrow-l" data-iconpos="left">Back</a>
            </div>
            <div data-role="content">
                <p id="RegisterMessage" class="message">Register a new user. If have registered previously, please go back and log in instead.</p>
                <form>
                    <label>Username</label>
                    <input type="text" id="RegisterUsername" placeholder="Choose your own username" />
                    <label>Password</label>
                    <input type="password" id="RegisterPassword" placeholder="Specify a password" />
                    <label>E-mail address</label>
                    <input type="text" id="RegisterEmail" placeholder="Specify your e-mail address" />
                    <input type="button" id="RegisterButton" value="Register" />
                </form>
            </div>
        </div>
        <div data-role="page" id="Login">
            <div data-role="header">
                <h1>Log in</h1>
                <a data-rel="back" data-icon="arrow-l" data-iconpos="left">Back</a>
            </div>
            <div data-role="content">
                <p id="LoginMessage" class="message">Log in with an existing account</p>
                <form>
                    <label>Username</label>
                    <input type="text" id="Username" placeholder="Type your username" />
                    <label>Password</label>
                    <input type="password" id="Password" placeholder="Type your password" />
                    <input type="button" id="LoginButton" value="Log in" />
                </form>
            </div>
        </div>
        <div data-role="page" id="Logout">
            <div data-role="header">
                <h1>Log out</h1>
                <a data-rel="back" data-icon="arrow-l" data-iconpos="left">Back</a>
            </div>
            <div data-role="content">
                <p id="LogoutMessage" class="message">Log out from the data service, making the data unavailable until you log in again.</p>
                <form>
                    <input type="button" id="LogoutButton" value="Log out" />
                </form>
            </div>
        </div>
        <div data-role="page" id="ShowData">
            <div data-role="header">
                <h1>Show data object</h1>
                <a data-rel="back" data-icon="arrow-l" data-iconpos="left">Back</a>
            </div>
            <div data-role="content">
                <h2 id="DataName"></h2>
                <p id="DataMessage" class="message"></p>
                <form>
                    <a data-rel="back" data-role="button">Back to start page</a>
                </form>
            </div>
        </div>
        <div data-role="page" id="EditData">
            <div data-role="header">
                <h1>Edit data object</h1>
                <a data-rel="back" data-icon="arrow-l" data-iconpos="left">Back</a>
            </div>
            <div data-role="content">
                <p id="EditDataMessage" class="message padded" style="display: none;"></p>
                <form>
                    <label>Name of data item</label>
                    <input type="text" id="EditDataName" />
                    <label>Content of data item</label>
                    <textarea id="EditDataValue" cols="20" rows="5"></textarea>
                    <input type="button" id="SaveDataButton" value="Save" />
                    <span id="DeleteDataButtonContainer"><input type="button" id="DeleteDataButton" value="Delete" /></span>
                </form>
            </div>
        </div>

        <script>
        var callbackUrl = "<?php if (isset($_SERVER['HTTP_REFERER'])) echo $_SERVER['HTTP_REFERER'];  ?>";
        var isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true':'false' ?>;
        var currentDataName = null;

        function onError(xhr, err, statusText) {
            if (err=="error" && statusText)
                err = statusText;
            err = "An error occurred! " + err;
            if (lastMessageId!=null)
                document.getElementById(lastMessageId).innerHTML = err;
            else
                alert(err);
        }

        function goToCallback() {
            if (callbackUrl && (callbackUrl<16 || callbackUrl.substring(callbackUrl.length-16, callbackUrl.length)!="/apps/filestore/"))
                window.location.href = callbackUrl;
            else
                $.mobile.changePage("#Start");
        }

        // Use custom function to show a message in a specified element and remember the id
        // and display any upcoming ajax error messages in the same element.
        var lastMessageId = null;
        function showMessage(messageId, messageText) {
            var msg = document.getElementById(messageId);
            if (!msg.defaultMessage)
                msg.defaultMessage = msg.innerHTML;
            msg.innerHTML = messageText;
            msg.style.display = "";
            lastMessageId = messageId;

            msg.style.backgroundColor = "#E0E000";
            window.setTimeout(function() {
                msg.style.MozTransition = "background-color 3s";
                msg.style.WebkitTransition = "background-color 3s";
                msg.style.backgroundColor = "";
                window.setTimeout(function() {
                    msg.style.MozTransition = "";
                    msg.style.WebkitTransition = "";
                    if (msg.defaultMessage)
                        msg.innerHTML = msg.defaultMessage;
                    else {
                        msg.innerHTML = "";
                        msg.style.display = "none";
                    }
                }, 3000);
            }, 100);
        }

        // Initialization when page is loaded
        $(function() {
            // Set default ajax settings for jQuery
            $.ajaxSetup({
                dataType: "json",
                error: onError
            });
            $.mobile.ajaxEnabled = false;
        });

        function register(username, password, email, success) {
            $.ajax({
                url: 'api/register.php',
                data: {
                    username: username,
                    password: password,
                    email: email
                },
                type: 'post',
                success: success
            });
        }

        function login(username, password, successFunc) {
            $.ajax({
                url: 'api/login.php',
                data: {
                    username: username,
                    password: password
                },
                type: 'post',
                success: function() {
                    isLoggedIn = true;
                    successFunc();
                }
            });
        }

        function logout(successFunc) {
            $.ajax({
                url: "api/logout.php",
                success: function() {
                    isLoggedIn = false;
                    successFunc();
                }
            });
        }

        function showData(dataName) {
            $("#DataName").text(dataName);
            $.mobile.changePage("#ShowData");
            $.ajax({
                url: "api/read.php",
                dataType: "json",
                data: {
                    name: dataName
                },
                success: function(data) {
                    $("#DataMessage").text(JSON.stringify(data));
                }
            });
        }

        var commands = [
            { id: "Login", title: "Log in with existing account" },
            { id: "Register", title: "Register new account" },
            { id: "Logout", title: "Log out" }
        ];
        $("#Start").bind("pageshow", function() {
            var html = "";
            for (var i = 0; i<commands.length; i++) {
                if ((isLoggedIn && i>0) || (!isLoggedIn && i<2))
                    html += "<li><a href=\"#" + commands[i].id + "\">" + commands[i].title + "</a></li>";
            }
            $("#CommandList").html(html).listview("refresh");
            $("#DataList").html("");
            $("#DataListMessage").text("");
            if (isLoggedIn) {
                $.ajax({
                    url: "api/list.php",
                    dataType: "json",
                    success: function(datalist) {
                        var html = "";
                        for (var i = 0; i<datalist.length; i++) {
                            html += "<li><a href=\"javascript:editData('" + datalist[i] + "')\">" + datalist[i] + "</a></li>";
                        }
                        html += "<li><a href=\"javascript:addData()\">Add data</a></li>";
                        $("#DataList").html(html).listview("refresh");
                        $("#DataListMessage").text("You are logged in and have the following " + datalist.length + " objects stored here:");
                    }
                });
            }
        });

        // Prepare to add a new data item
        function addData() {
            $.mobile.changePage("#EditData", { dataUrl: "#EditData" });
        }

        // Prepare to edit the existing data item
        function editData(dataName) {
            $.mobile.changePage("#EditData", { dataUrl: "#EditData?" + dataName });
        }

        // Initialize the EditData page for a data item to add/edit
        $("#EditData").bind("pageshow", function() {
            currentDataName = window.location.hash.indexOf("?") >= 0 ? window.location.hash.substring(window.location.hash.indexOf("?")+1) : null;
            if (currentDataName == null) {
                $("#EditDataName").val("").attr("readonly", false);
                $("#EditDataValue").val("");
                $("#DeleteDataButtonContainer").hide();
            }
            else {
                $("#EditDataName").val(currentDataName).attr("readonly", true);
                $("#EditDataValue").val("");
                $("#SaveDataButton").attr("disabled", true);
                $("#DeleteDataButtonContainer").show();
                $.ajax({
                    url: "api/read.php",
                    dataType: "json",
                    data: {
                        name: currentDataName
                    },
                    success: function(data) {
                        $("#EditDataValue").val(JSON.stringify(data));
                        $("#SaveDataButton").attr("disabled", false);
                    }
                });
            }
        });

        // When Register button is pressed
        $("#RegisterButton").click(function() {
            var username = $("#RegisterUsername").val();
            var password = $("#RegisterPassword").val();
            if (username.length==0 || password.length==0) {
                showMessage("RegisterMessage", "You must specify a requested username and password to register!");
                return;
            }
            showMessage("RegisterMessage", "Sending register request to server...");
            register(username, password, $("#RegisterEmail").val(), function() {
                login(username, password, goToCallback);
            });
        });

        // When Login button is pressed
        $("#LoginButton").click(function() {
            var username = $("#Username").val();
            var password = $("#Password").val();
            if (username.length==0 || password.length==0) {
                showMessage("LoginMessage", "You must specify a username and password to login!");
                return;
            }
            showMessage("LoginMessage", "Sending login request to server...");
            login(username, password, goToCallback);
        });

        // When Logout button is pressed
        $("#LogoutButton").click(function() {
            showMessage("LogoutMessage", "Sending logout request to server...");
            logout(goToCallback);
        });

        $("#SaveDataButton").click(function() {
            if ($("#EditDataName").val().length==0) {
                return;
            }
            var json = $("#EditDataValue").val();
            try {
                JSON.parse(json);
            }
            catch (err) {
                showMessage("EditDataMessage", "Not valid JSON. The value of the data item must be valid JSON.");
                return;
            }

            $.ajax({
                url: "api/write.php",
                dataType: "json",
                data: {
                    name: $("#EditDataName").val(),
                    value: json
                },
                success: function(data) {
                    $.mobile.changePage("#Start");
                }
            });
        });

        $("#DeleteDataButton").click(function() {
            if (currentDataName==null) {
                return;
            }
            $.ajax({
                url: "api/delete.php",
                dataType: "json",
                data: {
                    name: currentDataName
                },
                success: function(data) {
                    $.mobile.changePage("#Start");
                }
            });
        });
        </script>
    </body>
</html>

