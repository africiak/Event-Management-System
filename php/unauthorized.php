<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>
    <link rel="stylesheet" href="../css/landing.css">
    <style>
    body {
        background-color: rgb(224, 224, 224);

    }

    .unauthorized {
        font-family: 'poppins';
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 100px;
    }

    button {
        background-color: #FF6D1F;
        border: none;
        color: white;
        width: 300px;
        border-radius: 10px;
        padding: 10px;
        margin-top: 30px;
    }

    button:hover {
        border: 1px solid #FF6D1F;
        background-color: white;
        color: #FF6D1F;
    }
    </style>
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <a href="landing.html">HiveFlow
                    <img src="../img/logowhite.png" alt="bee icon" height="20px" weight="20px">
                </a>
            </div>
        </nav>
    </header>
    <div class="unauthorized">
        <strong> 404 - Page Not Found </strong>

        <p> Sorry, the page you're looking for doesn't exist. </p>

        <div class="logo">
            <img src="../img/taken.svg" alt="bee icon" width="300px" height="300px">
        </div>
        <a href="logout.php"><button>Back</button></a>
    </div>
    <script>
    function toggleMenu() {
        const menu = document.getElementById('navMenu');
        menu.classList.toggle('show');
    }
    </script>
</body>

</html>