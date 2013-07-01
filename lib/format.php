<?php
/**
 * Formatage automatique des champs
 *
 * @package    Vel
 * @subpackage Lib
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */

namespace Vel\Lib;

/**
 * Formatage automatique des champs
 *
 * @package    Vel
 * @subpackage Lib
 * @author     Adrien <aimbert@solire.fr>
 * @license    Solire http://www.solire.fr/
 */
class Format
{
    /**
     * Création d'un gestionnaire de format
     *
     * @param array $config Configuration de formatage
     */
    public function __construct($config)
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $value = preg_replace('#([[:space:]])#', '', $value);
            $config[$key] = explode(',', $value);
        }

        $this->_config = $config;
    }

    /**
     * Formate les champs prix
     *
     * @param array $data Données à formater
     *
     * @return array données formatées
     */
    public function formatPrice($data)
    {
        foreach ($this->_config['price'] as $name) {
            if (isset($data[$name])) {
                if (round($data[$name], 5) == (int)$data[$name]) {
                    $data[$name] = number_format($data[$name], 0, ',', ' ');
                    continue;
                }

                $data[$name] = number_format($data[$name], 2, ',', ' ');
            }
        }

        return $data;
    }

    /**
     * Formatage des champs date
     *
     * @param array $data Données à formater
     *
     * @return array données formatées
     */
    public function formatDate(array $data)
    {
        foreach ($this->_config['date'] as $name) {
            if (isset($data[$name])) {
                $data[$name] = \Slrfw\Format\DateTime::toText($data[$name], false);
            }
        }

        return $data;
    }

    /**
     * Formate les données
     *
     * @param array $data Données à formater
     *
     * @return array données formatées
     */
    public function formatAll($data)
    {
        foreach ($this->_config as $key => $champs) {
            $funcName = 'format' . ucfirst($key);
            $data = $this->$funcName($data);
        }

        return $data;
    }
}

