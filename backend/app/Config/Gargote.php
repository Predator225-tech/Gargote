<?php

namespace Config;
use CodeIgniter\Config\BaseConfig;
class Gargote extends BaseConfig {
    public int $nombreTables = 12;
    public int $quantiteMax = 20;
    public array $modesPaiement = ['especes'=>'Espèces','mvola'=>'MVola','orange_money'=>'Orange Money','airtel_money'=>'Airtel Money'];
}
