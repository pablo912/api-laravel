<!DOCTYPE html><html lang="en"><head><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <meta charset="utf-8">
  <title>Api Foxcont</title>
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="favicon.ico">

       <!-- Bootstrap Core CSS -->
       <style>@media print{*,::after,::before{text-shadow:none!important;box-shadow:none!important}}html{box-sizing:border-box;font-family:sans-serif;line-height:1.15;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;-ms-overflow-style:scrollbar;-webkit-tap-highlight-color:transparent}*,::after,::before{box-sizing:inherit}@-ms-viewport{width:device-width}body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;background-color:#fff}@-webkit-keyframes progress-bar-stripes{from{background-position:1rem 0}to{background-position:0 0}}</style><link href="./assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="./assets/plugins/bootstrap/css/bootstrap.min.css"></noscript>
       <!-- Custom CSS -->
       <style>@import url("https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700");*{outline:none}body{background:#fff;font-family:"Montserrat", sans-serif;margin:0;overflow-x:hidden;color:#67757c;font-weight:300}html{position:relative;min-height:100%;background:#ffffff;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}@-webkit-keyframes heartbit{0%{-webkit-transform:scale(0);opacity:0.0}25%{-webkit-transform:scale(0.1);opacity:0.1}50%{-webkit-transform:scale(0.5);opacity:0.3}75%{-webkit-transform:scale(0.8);opacity:0.5}100%{-webkit-transform:scale(1);opacity:0.0}}@-webkit-keyframes rotate{from{-webkit-transform:rotate(0deg)}to{-webkit-transform:rotate(360deg)}}@-webkit-keyframes ripple{0%{-webkit-box-shadow:0px 0px 0px 1px transparent;box-shadow:0px 0px 0px 1px transparent}50%{-webkit-box-shadow:0px 0px 0px 15px rgba(0, 0, 0, 0.1);box-shadow:0px 0px 0px 15px rgba(0, 0, 0, 0.1)}100%{-webkit-box-shadow:0px 0px 0px 15px transparent;box-shadow:0px 0px 0px 15px transparent}}@media (min-width: 768px){}@media (max-width: 767px){}</style><link href="./assets/css/style.css" rel="stylesheet" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="./assets/css/style.css"></noscript>
       <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous"> -->
       <!-- You can change the theme colors from here -->
       <!-- <link href="./assets/css/colors/default-blue.css" id="theme" rel="stylesheet"> -->
       <!-- font awesome -->
       <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous"> -->
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer">




       <style type="text/css">@font-face{font-family:'PT Sans';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/ptsans/v17/jizaRExUiTo99u79D0-ExcOPIDUg-g.woff2) format('woff2');unicode-range:U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;}@font-face{font-family:'PT Sans';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/ptsans/v17/jizaRExUiTo99u79D0aExcOPIDUg-g.woff2) format('woff2');unicode-range:U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;}@font-face{font-family:'PT Sans';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/ptsans/v17/jizaRExUiTo99u79D0yExcOPIDUg-g.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'PT Sans';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/ptsans/v17/jizaRExUiTo99u79D0KExcOPIDU.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu72xKKTU1Kvnz.woff2) format('woff2');unicode-range:U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu5mxKKTU1Kvnz.woff2) format('woff2');unicode-range:U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu7mxKKTU1Kvnz.woff2) format('woff2');unicode-range:U+1F00-1FFF;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4WxKKTU1Kvnz.woff2) format('woff2');unicode-range:U+0370-03FF;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu7WxKKTU1Kvnz.woff2) format('woff2');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu7GxKKTU1Kvnz.woff2) format('woff2');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Roboto';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/roboto/v30/KFOmCnqEu92Fr1Mu4mxKKTU1Kg.woff2) format('woff2');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmYmRlV9Su1caiTVo.woff) format('woff');unicode-range:U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISma2RlV9Su1caiTVo.woff) format('woff');unicode-range:U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmY2RlV9Su1caiTVo.woff) format('woff');unicode-range:U+1F00-1FFF;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmbGRlV9Su1caiTVo.woff) format('woff');unicode-range:U+0370-03FF;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmYGRlV9Su1caiTVo.woff) format('woff');unicode-range:U+0102-0103, U+0110-0111, U+0128-0129, U+0168-0169, U+01A0-01A1, U+01AF-01B0, U+1EA0-1EF9, U+20AB;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmYWRlV9Su1caiTVo.woff) format('woff');unicode-range:U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;}@font-face{font-family:'Roboto Slab';font-style:normal;font-weight:400;font-display:swap;src:url(https://fonts.gstatic.com/s/robotoslab/v24/BngbUXZYTXPIvIBgJJSb6s3BzlRRfKOFbvjojISmb2RlV9Su1cai.woff) format('woff');unicode-range:U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;}</style>
  <!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
<link rel="stylesheet" href="styles.b1f2caf7990d6ef4.css" media="print" onload="this.media='all'"><noscript><link rel="stylesheet" href="styles.b1f2caf7990d6ef4.css"></noscript></head>
<body class="fix-header card-no-border fix-sidebar">

 

      

  <app-root></app-root>



  <script src="./assets/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap tether Core JavaScript -->
  <script src="./assets/plugins/bootstrap/js/popper.min.js"></script>
  <script src="./assets/plugins/bootstrap/js/bootstrap.min.js"></script>
  <!-- slimscrollbar scrollbar JavaScript -->
  <script src="./assets/js/perfect-scrollbar.jquery.min.js"></script>
  <!--Wave Effects -->
  <script src="./assets/js/waves.js"></script>
  <!--Menu sidebar -->
  <script src="./assets/js/sidebarmenu.js"></script>
  <!--stickey kit -->
  <script src="./assets/plugins/sticky-kit-master/dist/sticky-kit.min.js"></script>
  <script src="./assets/plugins/sparkline/jquery.sparkline.min.js"></script>
  <!--Custom JavaScript -->
  <script src="./assets/js/custom.js"></script>
  <!-- ============================================================== -->
  <!-- Style switcher -->
  <!-- ============================================================== -->
  <script src="./assets/plugins/styleswitcher/jQuery.style.switcher.js"></script>
  <!-- <script type="text/javascript" src="https://unpkg.com/default-passive-events"></script> -->

 
<script src="runtime.5b35372a9b14dd3b.js" type="module"></script><script src="polyfills.74df6d475aac9a61.js" type="module"></script><script src="main.775f55ee036887a5.js" type="module"></script>

</body></html>