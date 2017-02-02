<?php

namespace App\Console;
use App\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Purchase extends Console
{
  protected function configure()
  {
    $this->setName("app:purchase")
      ->setDescription("Purchase podcasts if credits available");
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->getLogger()->info("Try to login");
    if($this->getEsl()->login()) {
      $this->getLogger()->info("Success login");
      if($coupon = $this->getEsl()->getCoupon()) {
        $this->getLogger()->info(sprintf("I have a coupon on %s podcasts", $coupon['remain']));
        $sql = sprintf("SELECT * FROM podcast WHERE purchased = 0 ORDER BY id DESC LIMIT %d", $coupon['remain']);
        $stmt = $this->getDb()->query($sql);
        $podcasts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if($podcasts) {
          $this->getLogger()->info(sprintf("Try to purchase %d podcasts", count($podcasts)));
          $this->getEsl()->purchase(array_column($podcasts, 'url'));
          $this->getLogger()->info(sprintf("Purchased"));

          $sql = "UPDATE podcast SET purchased = 1, purchased_dt = NOW() WHERE id IN (".implode(",", array_column($podcasts, "id")).")";
          $this->getDb()->query($sql);
          $this->getLogger()->info(sprintf("Updated in DB. Try to download."));
        }
      }
    } else {
      $this->getLogger()->error("Failed to login");
    }
  }
}