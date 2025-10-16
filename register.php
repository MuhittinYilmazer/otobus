<?php require_once 'header.php'?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
</head>
<body>
    <form action="index.php?page=register" method="post">
  <div class="form-group">
    <label for="exampleInputEmail1">Email address</label>
    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
    <small name="email" class="form-text text-muted">We'll never share your email with anyone else.</small>
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" name="password">
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Full Name</label>
    <input class="form-control" name="fullname">
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
</form>
</html>
<?php require_once 'footer.php'?>