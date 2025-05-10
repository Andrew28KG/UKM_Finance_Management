<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UKM Finance Management</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="shortcut icon" type="image/icon" href="images/logo.svg"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
      <nav id="navbar">
        <hr>
        <div class="navbar-container">
          <a href="index.php"><img src="images/logo.svg" class="nav-logo" alt="UKM Finance" width="60px"></a>
          <ul class="nav-main">
            <li class="nav-items home"><a href="index.php" id="home">Beranda</a></li>
            <li class="nav-items"><a href="transaksi.php">Transaksi</a></li>
            <li class="nav-items"><a href="laporan.php">Laporan Keuangan</a></li>
            <li class="nav-items"><a href="api/getxml.php">Download XML</a></li>
          </ul>
          <div class="nav-top-right">
            <?php if(isset($_SESSION['user_id'])): ?>
              <a href="profile.php"><i class="fa fa-user" style="color: #4F4C4D; font-size:24px"></i></a>
              <a href="logout.php"><i class="fa fa-sign-out" style="color: #4F4C4D; font-size:24px"></i></a>
            <?php else: ?>
              <a href="login.php"><i class="fa fa-sign-in" style="color: #4F4C4D; font-size:24px"></i> Login</a>
            <?php endif; ?>
          </div>
         </div>
       </nav> 