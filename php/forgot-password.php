<!--for forgot password func-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
        /* Center the form on the page */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Poppins";
            background-color: #fbfafa;
        }

        /* Styling for the form container */
        .login {
            background: white;
            padding: 60px;
            border-radius: 13.5px;
            box-shadow: 0 8px 16px rgba(0,0,0,.3);
            width: 300px;
            text-align: center;
        }

        /* Header styling */
        .login h1 {
            margin-bottom: 1em;
            color: black;
            font-size: 24px;
        }

        /* Label and input styling */
        .login label {
            display: block;
            margin-bottom: 0.5em;
            color: #555;
            text-align: left;
        }

        .login input[type="email"] {
            width: 100%;
            padding: 0.5em;
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        /* Button styling */
        .login button {
            width: 106%;
            padding: 0.75em;
            background-color: #ff2323;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login button:hover {
            background-color: #ad0000;
        }
    </style>
</head>
<body>
<div class="login">
    <form action="send-password-reset.php" method="post">
        <h1>Forgot Password</h1>
        <p>Please enter your Gmail address to receive the password reset link.</p>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required><br><br>
        <button type="submit">Send</button>
    </form>
</div>
</body>
</html>
