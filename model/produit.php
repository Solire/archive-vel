<?php
/**
 * Gabarit produit
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */

namespace Vel\Model;

/**
 * Gabarit produit
 *
 * @package    Vel
 * @subpackage Gabarit
 * @author     Adrien <aimbert@solire.fr>
 * @license    CC by-nc http://creativecommons.org/licenses/by-nc/3.0/fr/
 */
class Produit extends \Slrfw\Model\GabaritPage
{
    /**
     * Renvois le prix de la référence
     *
     * @param type $idRef
     * @param type $idRegion
     *
     * @return float prix de la référence
     */
    public function getPrice($idRef, $idRegion)
    {
        $references = $this->getBlocs('reference')->getValues();

        $reference = null;
        foreach ($references as $refSearch) {
            if ($refSearch['id'] == $idRef) {
                $reference = $refSearch;
                break;
            }
        }

        if (empty($reference)) {
            throw new \Slrfw\Exception\Lib('Aucune référence avec cet id');
        }

        if (!isset($reference['prix'][$idRegion])) {
            throw new \Slrfw\Exception\Lib('Référence non disponible pour cette réfion');
        }

        $prix = $reference['prix'][$idRegion]['prix_ttc'];

        return $prix;
    }

    /**
     * Renvois le premier id de référence disponible
     *
     * @return null|int
     */
    public function getDefaultIdRef()
    {
        $references = $this->getBlocs('reference');
        if (empty($references) || count($references) == 0) {
            return null;
        }
        $firstRef = $references->getValues();

        if (!isset($firstRef[0]['id'])) {
            return null;
        }

        return $firstRef[0]['id'];
    }
}

