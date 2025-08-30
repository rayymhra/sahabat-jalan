<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Email Verification</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Bebas Neue -->
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">

  <style>
    body {
      background-color: #f7fafe;
      color: #384a64;
      font-family: Arial, sans-serif;
    }

    h3 {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 2rem;
      color: #384a64;
    }

    p {
      color: #384a64;
      font-size: 1rem;
    }

    .card {
      border: none;
      border-radius: 12px;
      background: #fff;
    }

    .btn-primary {
      background-color: #5c99ee;
      border: none;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #2b6cb0;
    }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center min-vh-100">

  <div class="card shadow-sm p-4 text-center" style="max-width: 400px; width: 100%;">
    <h3>Verifikasi Email</h3>
    <p class="mt-3"><?php echo $message; ?></p>
    <a href="login.php" class="btn btn-primary mt-3 w-100">Kembali Ke Login</a>
  </div>

</body>
</html>
