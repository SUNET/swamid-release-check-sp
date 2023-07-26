<html>
<head>
  <meta charset="UTF-8">
  <title>Release check for SWAMID</title>
  <link href="//<?=$basename?>/fontawesome/css/fontawesome.min.css" rel="stylesheet">
  <link href="//<?=$basename?>/fontawesome/css/solid.min.css" rel="stylesheet">
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
  <link rel="manifest" href="/images/site.webmanifest">
  <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="/images/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="/images/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
  <style>
/* Space out content a bit */
body {
 padding-top: 20px;
 padding-bottom: 20px;
 <?= $Mode == 'QA' ? 'background-color: #F05523;' : ''?><?= $Mode == 'Lab' ? 'background-color: #8B0000;' : ''?>
}

.container {
      <?= ($Mode == 'QA' || $Mode == 'Lab') ? 'background-color: #FFFFFF;' : ''?>
}

/* Everything gets side spacing for mobile first views */
.header {
 padding-right: 15px;
 padding-left: 15px;
}

/* Custom page header */
.header {
 border-bottom: 1px solid #e5e5e5;
}
/* Make the masthead heading the same height as the navigation */
.header h3 {
 padding-bottom: 19px;
 margin-top: 0;
 margin-bottom: 0;
 line-height: 40px;
}
.left {
 float:left;
}
.clear {
 clear: both
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    max-width: 100%;
}

/* color for fontawesome icons */
.fa-check {
 color: green;
}

.fa-exclamation-triangle {
 color: orange;
}

.fa-exclamation {
 color: red;
}

/* Customize container */
@media (min-width: 768px) {
.container {
 max-width: 1230px;
}
}
.container-narrow > hr {
 margin: 30px 0;
}

/* Responsive: Portrait tablets and up */
@media screen and (min-width: 768px) {
/* Remove the padding we set earlier */
.header {
 padding-right: 0;
 padding-left: 0;
}
/* Space out the masthead */
.header {
 margin-bottom: 30px;
}
}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <nav>
        <ul class="nav nav-pills float-right">
          <li role="presentation" class="nav-item"><a href="https://www.sunet.se/swamid/" class="nav-link">About SWAMID</a></li>
          <li role="presentation" class="nav-item"><a href="https://www.sunet.se/swamid/kontakt/" class="nav-link">Contact us</a></li>
        </ul>
      </nav>
      <h3 class="text-muted"><a href="/index.php"><img src="https://<?=$basename?>/swamid-logo-2-100x115.png" width="55"></a> Release-check</h3>
    </div>
