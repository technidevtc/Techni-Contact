<?php
/*================================================================/

 Techni-Contact V3 - MD2I SAS
 http://www.techni-contact.com

 Auteur : OD pour Hook Network SARL - http://www.hook-network.com
 Date de création : 30 mai 2011

 Fichier : /includes/classV3/CReporting
 Description : Classe de calculs reporting

/=================================================================*/


class Reporting
{
	/* Connection Handle */
	private $db = NULL;
  private $interval = NULL;
  public $unregisteredCampaigns;
  public $campaignCollection;
  public $typeCollection;
  
	/* Reporting usefull vars */
	public $exist = false;
	public $lastErrorMessage = "";
  
	/* Constructor */
	function __construct($interval) {
		$this->db = DBHandle::get_instance();
    $this->interval = $interval;

    $this->campaignCollection = new stdClass();
    $this->typeCollection = new stdClass();

    $this->ProcessLeads();
    $this->ProcessCommands();
	}

  public function ProcessLeads(){

    $leadsCollectionByCampaignId = array();
    $leadsCollection = Lead::get($this->interval);
    require_once(ICLASS . '_ClassProduct.php');
    // on ne conserve que les leads facturables et facturés
    $nbr_suppliers = array();
    $nbr_leads = array();
    $nb_leads_primaires = array();

    foreach ($leadsCollection as $cle => $lead) {
      // leads suppliers counting
      $prod = new ProductsManager($this->db);
      $produit = $prod->GetProductsByIDs(array($lead['idProduct']));
      $idAdvertiser = $produit[$lead['idProduct']]['idAdvertiser'];
      $advertiser = new AdvertiserOld($idAdvertiser);
      if ($advertiser->category == __ADV_CAT_SUPPLIER__)
        $nbr_suppliers[$lead['campaignID']]++;

      if ($lead['parent'] == 0)
          $nb_leads_primaires[$lead['campaignID']]++;
      
      if (!($lead['invoice_status'] & __LEAD_CHARGEABLE__ || $lead['invoice_status'] & __LEAD_CHARGED__ )) {
        unset( $leadsCollection[$cle] );
        if (!is_array($leadsCollectionByCampaignId[$lead['campaignID']]))
          $leadsCollectionByCampaignId[$lead['campaignID']] = array();
      } else {
        $leadsCollectionByCampaignId[$lead['campaignID']][] = $lead;
      }

      $nbr_leads[$lead['campaignID']]++;
    }
    
    if (!empty($leadsCollectionByCampaignId)) {
      foreach($leadsCollectionByCampaignId as $idCampaign => $campaign){
        $nb_leads_primairesByIdCampaign = !empty ($nb_leads_primaires[$idCampaign]) ? $nb_leads_primaires[$idCampaign] : 0;
        $recupCampaign = array('leads_primaires' => $nb_leads_primairesByIdCampaign, 'nbr_leads' => 0, 'nbr_suppliers' => 0, 'income_total' => 0);

        $recupCampaign['nbr_suppliers'] = !empty($nbr_suppliers[$idCampaign]) ?  $nbr_suppliers[$idCampaign] : 0;
        $recupCampaign['nbr_leads'] = !empty($nbr_leads[$idCampaign]) ?  $nbr_leads[$idCampaign] : 0;

        foreach ($campaign as $lead) {
          if ($lead['parent'] == 0) {
            $recupCampaign['income_total'] += $lead['income'] != 0 ? $lead['income'] : $lead['income_total'];
          } else {
            $recupCampaign['income_total'] += $lead['income'];
          }
        }

        $campaign = new MktCampaign($idCampaign);

        if (!$campaign->exists) {
          $unregisteredCampaigns[] = $idCampaign;
          continue;
        }
        $recupCampaign['campaign_name'] = $campaign->nom;
        $recupCampaign['campaign_type'] = $campaign->id_mkt_campaigns_type;
        $recupCampaign['type_name'] = MktCampaign::getTypeName($campaign->id_mkt_campaigns_type);

        $collectionType[$recupCampaign['campaign_type']]['revenu_leads'] +=  $recupCampaign['income_total'];
        $collectionType[$recupCampaign['campaign_type']]['type_name'] =  $recupCampaign['type_name'];
        $collectionType[$recupCampaign['campaign_type']]['totalHT'] = 0;

        $this->campaignCollection->$idCampaign->leads = (object) $recupCampaign;
      }
    } else {
      foreach ($nb_leads_primaires as $idCampaign => $nb_leads_primairesByIdCampaign) {

        $recupCampaign = array('leads_primaires' => $nb_leads_primairesByIdCampaign, 'nbr_leads' => $nbr_leads, 'nbr_suppliers' => 0, 'income_total' => 0);
        $recupCampaign['nbr_leads'] = !empty($nbr_leads[$idCampaign]) ?  $nbr_leads[$idCampaign] : 0;
        
        $recupCampaign['nbr_suppliers'] = !empty($nbr_suppliers[$idCampaign]) ?  $nbr_suppliers[$idCampaign] : 0;

        $campaign = new MktCampaign($idCampaign);
        
        if (!$campaign->exists) {
          $unregisteredCampaigns[] = $idCampaign;
          continue;
        }
        $recupCampaign['campaign_name'] = $campaign->nom;
        $recupCampaign['campaign_type'] = $campaign->id_mkt_campaigns_type;
        $recupCampaign['type_name'] = MktCampaign::getTypeName($campaign->id_mkt_campaigns_type);
        $this->campaignCollection->$idCampaign->leads = (object) $recupCampaign;
      }

    }
    $this->typeCollection = (object) $collectionType;
    $this->unregisteredCampaigns = (object) $unregisteredCampaigns;
  }

  function processCommands() {

    $interval = implode(' AND ', $this->interval);
    $interval = preg_replace('/timestamp/i', 'validated', $interval);
    $interval = preg_replace('/campaignID/i', 'campaign_id', $interval);
    
    /*$interval[] = 'statut_paiement >= 10'; // on ne conserve que les commandes payées et non annulées
    $interval[] = 'statut_paiement < 99';
    $interval[] = 'statut_traitement != 99';
    $this->interval = $interval;*/
    
    $collectionType = (array) $this->typeCollection;
    $unregisteredCampaigns = (array) $this->unregisteredCampaigns;

    $commandsCollectionByCampaignId = array();
    $commandsCollection = Doctrine_Query::create()
      ->select('campaign_id, total_ht, total_ttc')
      ->from('Order')
      ->where($interval)
      ->fetchArray();
    //$commandsCollection = Command::getFromInterval($this->interval);
    
    if (!empty($commandsCollection)) {
      foreach ($commandsCollection as $command)
        $commandsCollectionByCampaignId[$command['campaign_id']][] = $command;
    }
    
    foreach ($commandsCollectionByCampaignId as $idCampaign => $campaign) {
      $recupCampaign = array('nbr_commands' => 0, 'totalHT' => 0);
      foreach ($campaign as $command) {
        $recupCampaign['totalHT'] += $command['total_ht'];
        $recupCampaign['nbr_commands']++;

        $campaign = new MktCampaign($idCampaign);
        if (!$campaign->exists) {
          $unregisteredCampaigns[] = $idCampaign;
          continue;
        }
        $recupCampaign['campaign_name'] = $campaign->nom;
        $recupCampaign['campaign_type'] = $campaign->id_mkt_campaigns_type;
        $recupCampaign['type_name'] = MktCampaign::getTypeName($campaign->id_mkt_campaigns_type);

        if (is_array($collectionType[$recupCampaign['campaign_type']])) {
          $collectionType[$recupCampaign['campaign_type']]['totalHT'] +=  $command['total_ht'];
          $collectionType[$recupCampaign['campaign_type']]['type_name'] =  $recupCampaign['type_name'];
        } else {
          $collectionType[$recupCampaign['campaign_type']]['totalHT'] =  $recupCampaign['totalHT'];
          $collectionType[$recupCampaign['campaign_type']]['type_name'] =  $recupCampaign['type_name'];
          $collectionType[$recupCampaign['campaign_type']]['revenu_leads'] = 0;
        }

        $this->campaignCollection->$idCampaign->commands = (object) $recupCampaign;
      }

      $this->typeCollection = (object) $collectionType;
      $this->unregisteredCampaigns = (object) $unregisteredCampaigns;
    }
  }
}
