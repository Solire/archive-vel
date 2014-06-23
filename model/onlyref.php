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
class OnlyRef extends \Slrfw\Model\GabaritPage
{

    /**
     * Retourne le formulaire de création/d'édition de la page
     *
     * @param string $action        adresse de l'action du formulaire
     * @param string $retour        adresse de retour
     * @param array  $redirections  tableau des redirections
     *
     * @return string formulaire au format HTML
     */
    public function getForm($action, $retour, $redirections = array(), $authors = array())
    {
        $this->view = array();

        $this->view['action'] = $action;
        $this->view['retour'] = $retour;
        $this->view['authors'] = $authors;

        if (count($redirections) == 0) {
            $this->view['redirections'] = array('');
        } else {
            $this->view['redirections'] = $redirections;
        }

        $this->view['versionId'] = $this->_version['id'];
        $this->view['metaId'] = isset($this->_meta['id']) ? $this->_meta['id'] : 0;
        $this->view['metaLang'] = isset($this->_meta['id_version']) ? $this->_meta['id_version'] : BACK_ID_VERSION;
        $this->view['noMeta'] = !$this->_gabarit->getMeta() || !$this->view['metaId'] ? ' style="display: none;" ' : '';
        $this->view['noMetaTitre'] = !$this->_gabarit->getMeta_titre() ? ' style="display: none;" ' : '';
        $this->view['noRedirections301'] = !$this->_gabarit->get301_editable() ? ';display: none' : '';
        $this->view['parentSelect'] = '';
        $this->view['allchamps'] = $this->_gabarit->getChamps();
        $this->view['api'] = $this->_gabarit->getApi();

        ob_start();
        include __DIR__ . '/onlyref.phtml';
        $form = ob_get_clean();

        $form = preg_replace('#<script.+</script>#Us', '', $form);

        return $form;
    }
}
