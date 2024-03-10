<p><?php echo PrinterClass::t("Please run the following cron jobs in your server as http")?><br/>
<?php echo PrinterClass::t("set the running of cronjobs every minute")?>
</p>

<ul>
 <li>
  <a href="<?php echo websiteUrl()."/printer/cron/processprint"?>" target="_blank">
  <?php echo websiteUrl()."/printer/cron/processprint"?>
  </a>
 </li>
 <li>
  <a href="<?php echo websiteUrl()."/printer/cron/querystatus"?>" target="_blank">
  <?php echo websiteUrl()."/printer/cron/querystatus"?>
  </a>
 </li>
</ul>


<p><?php echo PrinterClass::t("Eg. command")?><br/>
curl <?php echo websiteUrl()."/printer/cron/processprint"?> <br/>
curl <?php echo websiteUrl()."/printer/cron/querystatus"?>
</p>