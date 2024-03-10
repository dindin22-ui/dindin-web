<!DOCTYPE html><html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>pdfview</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;1,900&display=swap" rel="stylesheet">
</head>
<header>
    <img src="<?php echo $alldata['logo_url']; ?>" style="width: 180px;" alt="">
</header>
<body style="">
    <table style="width: 100%; border-collapse: separate; border-spacing: 1px; margin: 30px 0 5px 0;  ">
        <tbody>
            <tr style="padding: 3px;">
                <td style="width: 30%; padding: 10px; ">
                    <p style="font-weight: bold; font-size: 20px; font-family: Roboto;">SUMMARY REPORT</p>
                    <p style="font-weight: bold; font-size: 20px;"> <?php echo  $alldata["from"] ?> <?php echo  $alldata["to"]; ?></p>
                </td>
                <td style="width: 30%; padding: 10px 10px 0px 0px ; ">
                    <p style="font-weight: bold; font-size: 20px;  margin-bottom:0px; font-family: Roboto;"><?php echo  $alldata["restaurant_name"];?></p>
                    <p style=" font-size: 14px; font-weight: 400;  margin-bottom:0px; margin-top: 5px;font-family: Roboto; "><?php echo  $alldata["street"]; ?> <br> <?php echo  $alldata["city"]; ?> <?php echo  $alldata["state"]; ?> OR <?php echo  $alldata["postal_code"]; ?> </p>
                </td>
            </tr>
        </tbody>
    </table>
    <table style=" width: 40%; border-collapse: collapse; border-spacing: 1px; margin: 15px 0 0 0; border-bottom: 2pt solid #ddd; float: left;">
        <thead style="border-bottom: 2pt solid #ddd;">
            <tr style="padding: 1px; font-size: 14px; color: #000000;">
                <th style="width: 15%; border: .5px solid #ddd; font-size: 14px; padding: 8px; text-align: left; font-weight: bold; font-family: Roboto;">Number of Orders</th>
                <td style="width: 15%; border: .5px solid #ddd; font-size: 14px; padding: 8px; text-align: right; font-weight: 400; font-family: Roboto;"><?php echo  $alldata['orders']; ?> </td>
            </tr>
        </thead>
        <tbody>
            <tr style="padding: 1px; font-size: 14px; font-weight: 400; font-family: Roboto;">
                <th style="font-weight: bold; font-family: Roboto; width: 20%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: left; vertical-align: top;">Total Tips</th>
                <td style="width: 10%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: right; vertical-align: top;">$<?php echo $alldata['total_tips']; ?> </td>
            </tr>
            <tr style="padding: 1px; font-size: 14px; font-weight: 400; font-family: Roboto;">
                <th style="font-weight: bold; font-family: Roboto; width: 20%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: left; vertical-align: top;">Total</th>
                <td style="font-weight: 400; font-family: Roboto; width: 10%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: right; vertical-align: top;">$<?php echo  $alldata['total_w_tax'];?></td>
            </tr>
            <tr style="padding: 1px; font-size: 14px; font-weight: 400; font-family: Roboto;">
                <th style="font-weight: bold; font-family: Roboto; width:20%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: left; vertical-align: top;">Total Commission/Convenience fee</th>
                <td style="font-weight: 400; font-family: Roboto; width: 10%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: right; vertical-align: top;">$<?php echo  $alldata['total_commission']; ?></td>
            </tr>
            <tr style="padding: 1px; font-size: 14px;">
                <th style="font-weight: bold; font-family: Roboto; width: 20%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: left; vertical-align: top;">Distribute to restaurant by DinDin</th>
                <td style="font-weight: 400; font-family: Roboto; width: 10%; border: .5px solid #ddd; padding: 8px; font-size: 14px; text-align: right; vertical-align: top;">$<?php echo  $alldata['totalMinusFeeComm']; ?> </td>
            </tr>
        </tbody>
    </table>
    <table style="width: 50%;  border-collapse: separate;  clear:both; border-spacing: 1px; float: right;">
        <tbody><tr><td><h3 style="font-family: Roboto; margin: 0px 0px; ">Account</h3>
                    <p style="margin: 5px 0px; font-family: Roboto, font-weight: 400; sans-serif;"> <?php echo  $alldata["restaurant_name"] . '(Merchant ID #'.  $alldata["merchant_id"].')' ; ?></p>
                </td>
            </tr>
            <tr><td>
                    <!--<h3 style="margin: 5px 0px; font-family: Roboto;">Commission Option</h3>-->
                    <!--<p style="margin: 5px 0px; font-weight: 400; font-family: Roboto;">Transaction fee <?php //echo  $alldata["is_commission"] ?> %</p>-->
                    <!--<p style="margin: 5px 0px; font-weight: 400; font-family: Roboto;">convience fee <?php //echo  $alldata["any_fee"]?>%</p>-->
                </td>
            </tr>
            <tr><td><h3 style="margin: 5px 0px; font-family: Roboto;">Questions </h3>
                    <p style="margin: 5px 0px; font-weight: 400; font-family: Roboto;">Contact Omnitech LLC</p>
                    <p style="margin: 5px 0px; font-weight: 400; font-family: Roboto;">hello@omnitech.pro</p>
                </td>
            </tr>
        </tbody>
    </table><hr style = "border:none; height:10px; width:100%; clear:both;" >
    <!--<p  style="font-weight: 400; font-family: Roboto; font-size:14px;">You can access your account online 24/7 Call your account advisor today for more Information</p>-->
    <!--<p style=" font-weight: 400; font-family: Roboto; font-size:14px; ">Sincerely yours,</p>-->
    <!--<p  style=" font-weight: 400; font-family: Roboto; font-size:14px; ">Omnitech LLC</p>-->
    
    <p  style="font-weight: 400; font-family: Roboto; font-size:14px;">You can access your account online 24/7 Call your account advisor today for more Information<br>
    Sincerely yours,<br>
    Omnitech LLC</p>
</body>
</html>