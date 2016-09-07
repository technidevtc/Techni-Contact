<?php

require_once(INCLUDES_PATH.'fpdf/fpdf.php');

class PDFInvoiceModelMOB extends FPDF {

  function Header() {
    $x = $this->x = 20;
    $y = $this->y = 10; // make sure to have 0.1 rounded values
    $lMargin = $this->lMargin;
    $tMargin = $this->tMargin;
    $rMargin = $this->rMargin;
    $this->SetMargins($x,$y,$x);
    $this->Image(SECURE_PATH.'ressources/images/logo-website-mobaneo.jpg',120,10,80);
    $this->SetFont('Arial','B',10);
    $this->SetY($y+2);
    $this->Cell(80,4,"Mobaneo / M.D2i",0,1);
    $this->SetFont('','');
    $this->MultiCell(80,4,
      "253 rue Gallieni\n".
      "F-92774 BOULOGNE BILLANCOURT cedex\n".
      "S.A.S. au capital de 160 000 €\n".
      "RCS Nanterre B 392 772 497\n".
      "Tva Intra. : FR12 392 772 497\n",0);
    $this->Cell(80,4,"http://www.mobaneo.com/",0,0);
    $this->SetMargins($lMargin,$tMargin,$rMargin);
    $this->SetY($y+35);
  }
  
  function Footer() {
  }
  
  // get multi cell line count without writing it
  // code taken directly from MultiCell in FPDF, without border/alignment/output management
  function GetNbLines($w, $h, $txt, $border=0, $align='J', $fill=false) {
    $cw = &$this->CurrentFont['cw'];
    if ($w==0)
      $w = $this->w-$this->rMargin-$this->x;
    $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
    $s = str_replace("\r",'',$txt);
    $nb = strlen($s);
    if ($nb>0 && $s[$nb-1]=="\n")
      $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while($i<$nb) {
      $c = $s[$i];
      if($c=="\n") {
        $i++;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
        continue;
      }
      if ($c==' ') {
        $sep = $i;
        $ls = $l;
      }
      $l += $cw[$c];
      if ($l>$wmax) {
        if ($sep==-1) {
          if ($i==$j)
            $i++;
        }
        else
          $i = $sep+1;
        $sep = -1;
        $j = $i;
        $l = 0;
        $nl++;
      }
      else
        $i++;
    }
    return $nl;
  }
  
  /*private $source; // source
  function setSource($source) {
    $this->source = $source;
  }*/
  
  function writeTitle($title) {
    $this->x += 10;
    $this->SetFont('Arial','B',20);
    $this->Cell(80,5,$title,0,1);
    $this->y += 5;
  }
  
  function writeCGV() {
    $this->AddPage();
    $this->SetMargins(20,10,20);
    $this->SetFont('','B',13);
    $this->Ln();
    $this->Cell(0,5,"CONDITIONS GENERALES DE VENTE",0,1,'C');
    $this->Ln();
    $this->SetFont('','B',7);
    $this->Cell(0,3,"ENTRE :",0,1);
    $this->SetFont('');
    $this->Write(3,
"
LA SOCIETE MD2I, Société par actions simplifiée (SAS) au capital de 160.000,00 euros, immatriculée au Registre du Commerce et des Sociétés de NANTERRE sous le numéro B 392 772 497, sise  253, rue Gallieni,  92774 BOULOGNE BILLANCOURT.

Représentée par son Président Directeur Général Monsieur Frédéric STUMM

Ci-après dénommée « SAS MD2I»

");
    $this->SetFont('','B');
    $this->Cell(0,3,"ET :",0,1);
    $this->SetFont('');
    $this->Write(3,
"
Le client professionnel ou la collectivité locale

Ci après dénommée « LE CLIENT PROFESSIONNEL »

La SAS MD2I et LE CLIENT PROFESSIONNEL sont ci-après désignés collectivement, les « PARTIES », et individuellement une « PARTIE »,

");
    $this->SetFont('','B');
    $this->Cell(0,3,"IL A PREALABLEMENT ETE EXPOSE CE QUI SUIT :",0,1);
    $this->SetFont('');
    $this->Write(3,
"
La SAS MD2I est la société éditrice et exploitante du site www.mobaneo.com.

Ce site est dédié aux professionnels. A ce titre, il propose à la vente des matériels, fournitures, équipements dont les professionnels et collectivités ont besoin dans le cadre de leur activité.

En souscrivant aux présentes Conditions Générales de Vente, LE CLIENT PROFESSIONNEL déclare que l’achat qu’il effectue sur le SITE répond directement à un besoin professionnel et qu’il commande le produit ou service directement en sa qualité de professionnel ; reconnaissant ainsi qu’il ne saurait en aucun cas être considéré comme un consommateur ou un non professionnel au sens de la Loi française.

");
    $this->SetFont('','B');
    $this->Cell(0,3,"Important :",0,1);
    $this->SetFont('');
    $this->Write(3,
"
Toute commande effectuée sur le site www.mobaneo.com implique obligatoirement l'acceptation sans réserve, par LE CLIENT PROFESSIONNEL, des présentes Conditions Générales de Vente.

Seules les présentes constituent les Conditions Générales de Vente. Elles sont seules applicables à la relation contractuelle.

Elles remplacent et annulent toutes autres conditions antérieures sauf dérogation expresse et écrite de la Société MD2I.

");
    $this->SetFont('','B');
    $this->Cell(0,3,"C’EST DANS CES CONDITIONS QUE LES PARTIES ONT ARRETE ET CONVENU CE QUI SUIT :",0,1);
    
    // article 1
    $this->writeCGVtitle("DEFINITIONS");
    $this->Write(3,
"Les termes, mentionnés ci-dessous, ont dans les présentes CONDITIONS GENERALES DE VENTE, la signification suivante :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« BOUTIQUE EN LIGNE »");
    $this->SetFont('');
    $this->Write(3,
" : Site internet marchand de la SAS MD2I accessible depuis l’adresse http://www.mobaneo.com.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« CONDITIONS GENERALES DE VENTE »");
    $this->SetFont('');
    $this->Write(3," ou ");
    $this->SetFont('','B');
    $this->Write(3,"« CGV »");
    $this->SetFont('');
    $this->Write(3,
" : désigne le présent contrat réputé accepté par les PARTIES.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« LE CLIENT PROFESSIONNEL »");
    $this->SetFont('');
    $this->Write(3,
" : toute personne physique majeure et capable ou toute personne morale agissant par l’intermédiaire d’une personne physique certifiant disposer de la capacité juridique pour contracter au nom et pour le compte de la personne morale, contractant avec la SAS MD2I.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« COMMANDE VALIDEE »");
    $this->SetFont('');
    $this->Write(3,
" : une commande ne devient une Commande Validée qu’à partir de la réception, par la SAS MD2I, du paiement effectif de la somme due (prix principal, intérêts et accessoires) par le Client tant pour les Produits ou Services Commandés qu’au titre de la Livraison.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« PRODUIT COMMANDE »");
    $this->SetFont('');
    $this->Write(3,
" : s’entend du Produit ou du Service désigné par le CLIENT PROFESSIONNEL lors du processus de commande ou de tout autre produit similaire de même valeur et ayant des caractéristiques substantielles identiques.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"•");
    $this->Write(3,"« LIVRAISON »");
    $this->SetFont('');
    $this->Write(3,
" : La livraison est considérée comme réalisée dès la date de la première présentation des PRODUITS OU SERVICES COMMANDES à l’adresse de livraison mentionnée lors de la commande par le Client.
");
    $this->SetLeftMargin(20);
    
    // article 2
    $this->writeCGVtitle("OBJET");
    $this->Write(3,
"Les présentes CGV ont pour objet de fixer les dispositions contractuelles relatives aux droits et obligations respectifs des PARTIES dans le cadre de leurs relations contractuelles.
");
    
    
    // article 3
    $this->writeCGVtitle("MODIFICATIONS DES PRESENTES CONDITIONS GENERALES DE VENTE");
    $this->Write(3,
"La SAS MD2I pourra modifier à tout moment les présentes CGV. 

Seules les CGV publiées le jour de la commande sont applicables. Elles sont portées à la connaissance du client lors du processus de commande et avant le paiement.
");
    $this->AddPage();
    $this->Write(3,
"La SAS MD2I se réserve le droit d’appliquer des conditions générales de vente particulières lorsque cela lui apparaît nécessaire. Il en est NOTAMMENT ainsi lorsque :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande présente un risque financier excessif (état de cessation de paiement du CLIENT PROFESSIONNEL, références commerciales jugées insuffisantes par la SAS MD2I, CLIENT PROFESSIONNEL nouveau ou irrégulier dans ces commandes);
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande provient d’un CLIENT PROFESSIONNEL n’ayant pas acquitté l’ensemble de ses obligations nées d’affaires antérieures ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande provient d’un CLIENT PROFESSIONNEL ayant manifesté un comportement déloyal ou contraire aux usages commerciaux.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Dans le cas d’un risque financier excessif présenté par LE CLIENT PROFESSIONNEL, la SAS MD2I pourra exigée des garanties telles que l’Administrateur judiciaire et/ou des modalités de paiement particulières (paiement avant le départ des marchandises de la SAS MD2I, paiement à la livraison…).
");
    
    // article 4
    $this->writeCGVtitle("PRODUIT OU SERVICE COMMANDE");
    $this->writeCGVtitle("Les produits en vente directe",1);
    $this->Write(3,
"Tous les PRODUITS COMMANDES sont décrits par la SAS MD2I aussi précisément que possible en fonction des seules informations communiquées par le fournisseur.

Les caractéristiques figurant sur les catalogues, fiches produits, fiches techniques, visuels produits ou tout autre document sont données à titre indicatif et ne sauraient en aucun cas engagées la responsabilité de la SAS  MD2I.

En cas de doute sur les caractéristiques d’un PRODUIT COMMANDE, le Client doit contacter la SAS MD2I au 01 83 62 96 95.

Les photographies des PRODUITS COMMANDES sont communiquées à titre illustratif et ne peuvent par conséquent engager la responsabilité de la SAS MD2I, ni remettre en cause une COMMANDE VALIDEE, que s’il existe d’éventuelles différences portant sur des qualités substantielles du produit. 

Une différence portant sur la couleur, les fonctionnalités secondaires ou les accessoires d’un produit ne peut en aucun engager la responsabilité de la SAS MD2I, ni remettre en cause une COMMANDE VALIDEE.

Les PRODUITS COMMANDES et les offres promotionnelles proposés par la SAS MD2I ne sont valables que dans la limite des stocks disponibles.
");
    
    // article 5
    $this->writeCGVtitle("CONFIRMATION DE LA COMMANDE");
    $this->Write(3,
"Une fois le PRODUIT COMMANDE sélectionné, LE CLIENT PROFESSIONNEL émet un bon de commande à la SAS MD2I soumis à l’acceptation de celle-ci.

Ce bon de commande n’engage la SAS MD2I que si celui-ci est confirmé par elle.

LE CLIENT PROFESSIONNEL recevra la confirmation de la SAS MD2I de l’enregistrement de son bon de commande par courrier électronique à l’adresse mail qu’il aura communiqué pour les besoins de sa commande.
");
    
    // article 6
    $this->writeCGVtitle("TRANSFERT DE PROPRIETE");
    $this->Write(3,
"Le transfert de propriété ne s’effectue qu’une fois la COMMANDE VALIDEE, c’est-à-dire au complet paiement du prix (prix principal, intérêts et accessoires) par le CLIENT PROFESSIONNEL. Dans l’attente du paiement  effectif du prix, la SAS MD2I se réserve la propriété du PRODUIT COMMANDE.

Le transfert de propriété a lieu, en cas de paiement par chèque ou effet de commerce, qu’au moment de l’encaissement effectif de ces derniers.
");    
    
    // article 7
    $this->writeCGVtitle("LES CONDITIONS FINANCIERES");
    $this->writeCGVtitle("Le prix",1);
    $this->Write(3,
"Le prix de vente du PRODUIT COMMANDE est indiqué en devise euro (€) hors taxes (€ HT) hors coût de livraison pour un produit commandé pris dans les locaux de la SAS MD2I, non emballé.

Le prix de livraison du PRODUIT COMMANDE est indiqué en devise euro (€) hors taxes (€ HT)

Ils sont indiqués au CLIENT PROFESSIONNEL au moment de l’élaboration de son bon de commande et ce avant la phase de paiement. 

Il est entendu entre les PARTIES que le CLIENT PROFESSIONNEL prendra à sa charge, en sus de ce qui apparaitra dans le bon de commande :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Le prix du montage et de la mise en route du matériel si besoin est ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Le prix de l’emballage exécuté conformément aux usages afin de garantir le transport du PRODUIT COMMANDE dans les meilleures conditions ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Les taxes, droits, frais, timbres et primes d’assurance.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Les prix des PRODUITS COMMANDES peuvent évoluer à tout moment en fonction de la politique tarifaire. Les PRODUITS COMMANDES sont facturés au prix en vigueur lors de l'enregistrement de la commande.

Les prix des PRODUITS COMMANDES et le prix de livraison ne sont valables qu'en France Continentale (Hors Corse, DOM/TOM et étranger).

Les frais engendrés par une livraison à l’étranger, Corse ou DOM/TOM sont à la charge du CLIENT PROFESSIONNEL (impôts, taxes, redevances, frais de contrôle technique, frais éventuellement dus à l’application d’une législation étrangère…). Il peuvent faire l'objet d'un devis spécifique.
");
    $this->AddPage();
    $this->writeCGVtitle("Le règlement",1);
    $this->Write(3,
"Toutes les commandes effectuées sur la BOUTIQUE EN LIGNE sont payables à compter du jour de la commande.

Le délai de validation de la commande est donc tributaire du mode de paiement choisi qui s’effectue directement auprès de la SAS MD2I, soit :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"1 jour ouvré pour le paiement par carte bancaire (visa/mastercard),
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"1 jour ouvré suite à réception du virement ou du chèque de règlement
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"1 jour ouvré à compter de la réception des éléments nécessaires au règlement par mandat administratif
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Les traites qui sont éventuellement jointes aux factures pour acceptation doivent être retournées à la SAS MD2I dans les quarante-huit (48) heures.

");
    $this->writeCGVtitle("Défaut de paiement",1);
    $this->Write(3,
"Tout retard de paiement du prix entraîne l’application de pénalités de retard d’un taux d’intérêt égal à une fois et demie le taux d’intérêt légal pour tout paiement intervenu plus d’un jour après la facturation de la commande par la SAS MD2I sauf autorisation préalable, expresse et écrite de celle-ci.

Dans le cas où le paiement se révélerait être irrégulier, incomplet ou inexistant, en raison d’une faute qui est imputable au CLIENT PROFESSIONNEL, le bon de commande serait annulé, les frais en découlant étant à la  charge du CLIENT PROFESSIONNE, une action civile et/ou pénale pouvant, le cas échéant, être entreprise à son encontre.
");

    // article 8
    $this->writeCGVtitle("TRANSFERT DES RISQUES");
    $this->writeCGVtitle("En France",1);
    $this->Write(3,
"Le CLIENT PROFESSIONNEL supporte les risques pesant sur le/les PRODUIT(S) COMMANDE(S) dès :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"L’échange des consentements entre les PARTIES, c’est-à-dire l’acceptation par la SAS MD2I du bon de commande émis par le CLIENT PROFESSIONNEL ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"la mise à disposition dans les locaux de MD2I du/des PRODUITS PARTENAIRES avant tout chargement.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->writeCGVtitle("A l’étranger",1);
    $this->Write(3,
"Le transfert des risques s’effectuera conformément à l’incoterm figurant sur la confirmation de la commande.
");
    
    // article 9
    $this->writeCGVtitle("LA LIVRAISON");
    $this->writeCGVtitle("Les modalités de la LIVRAISON",1);
    $this->Write(3,
"Les PRODUITS COMMANDES sur la Boutique en Ligne peuvent être livrés en France Continentale, ou en Corse, DOM/TOM et étranger sous certaines conditions visées supra. 

Les LIVRAISONS s'effectuent du lundi au vendredi. La SAS MD2I s’engage à respecter les délais de DISPONIBILITE indiquée pour les PRODUITS COMMANDES en vente directe sur le site www.mobaneo.com, excepté les  mois de Juillet et d’Août ou ces derniers pourront être rallongés sans préavis. 

Le délai de LIVRAISON commence à courir une fois la COMMANDE VALIDEE. 

La remise de la marchandise sera effectuée contre signature par le CLIENT PROFESSIONNEL réceptionnaire d'un bon de livraison. Le CLIENT PROFESSIONNEL a pour obligation de vérifier la conformité de la marchandise livrée au moment de la LIVRAISON, avant de signer le bon de livraison.

Toute anomalie concernant la LIVRAISON (avarie, produit manquant par rapport au bon de livraison, colis endommagé, produits cassés...) devra être impérativement indiquée sur le bon de livraison sous forme de \"réserves manuscrites\", accompagnée de la signature du CLIENT PROFESSIONNEL.

");
    $this->writeCGVtitle("Les complications liées à la LIVRAISON",1);
    $this->Write(3,
"Toute modification faite par LE CLIENT PROFESSIONNEL d’un ordre de livraison en cours, soumise au préalable à l’acceptation de la SAS MD2I, entraine une prorogation de ce délai prévue selon les modalités définis par elle.

Si la configuration réelle du lieu de livraison empêche physiquement le déchargement du ou des articles, le Vendeur se réserve le droit d'annuler la livraison et de procéder au remboursement de l'article, déduction  faite du coût de livraison et du coût de retour.

En cas de vente à l’exportation, les différentes autorisations (licence d’importation, autorisation de transfert de devises), devront avoir été obtenues préalablement par le client.

Lorsque le client refuse de réceptionner, la SAS MD2I est en droit de mettre le/les PRODUIT(S) COMMANDE(S) en entrepôt aux frais du client, comprenant également les frais de transport et de manutention. 

Si le CLIENT PROFESSIONNEL ne procède pas au retrait de ce/ces PRODUIT(S) COMMANDE(S) quinze (15) jours après sa/leur mise(s) à disposition, la SAS MD2I pourra résilier unilatéralement le contrat et procéder à la revente des marchandises. La SAS MD2I pourra exiger du CLIENT PROFESSIONNEL de payer la différence de prix initial convenu entre les PARTIES et le prix de revente qu’à obtenu la SAS MD2I.

Il en va de même, lorsque le CLIENT PROFESSIONNEL est tenu directement de procéder au retrait du/des PRODUIT(S) COMMANDE(S) dans les entrepôts de la SAS MD2I.
");
    
    $this->AddPage();
    
    // article 10
    $this->writeCGVtitle("RESPONSABILITE");
    $this->writeCGVtitle("La LIVRAISON",1);
    $this->Write(3,
"La responsabilité de la SAS MD2I, ne pourra pas être retenue en cas :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"de retard ou difficultés de livraison dus aux grèves, manque de camion ou wagon, incendie, dans l’approvisionnement, dans la fabrication du PRODUIT COMMANDE,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"du non-paiement du CLIENT PROFESSIONNEL,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"de force majeure ou fortuite, événements de guerre ou troubles intérieurs.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"La responsabilité de la SAS MD2I ne pourra en aucun cas être engagée en cas d’inexécution ou de mauvaise exécution des prestations contractuelles imputable au CLIENT PROFESSIONNEL notamment lors de la saisie de la commande ; ou à tout autre cas de force majeure.

Dans tous les cas, la SAS MD2I n’est tenue qu’à une simple obligation de moyen. Toute mise en cause de sa responsabilité nécessite la preuve d’une faute commise par elle.

");
    $this->writeCGVtitle("Les vices-cachés",1);
    $this->Write(3,
"La SAS MD2I décline toute responsabilité qui résulterait d’un accident de personnes ou de biens provenant d’un vice-caché d’un PRODUIT COMMANDE

De même, elle ne saurait verser une quelconque indemnité à titre de dommages et intérêts exigé par un CLIENT PROFESSIONNEL dont le PRODUIT COMMANDE contient un vice caché.
");

    // article 11
    $this->writeCGVtitle("GARANTIES");
    $this->Write(3,
"Les produits vendus par la SAS MD2Ii sont soumis la garantie légale de conformité et la garantie \"Vices cachés\", conformément aux articles L211-4 à L211-14 du Code de la Consommation et des articles 1641 et suivants du code civil.

Les produits vendus bénéficient en outre d'une garantie contractuelle d'un an minimum ou selon mention spécifique indiquées sur les fiches produit ou les devis.

Cette garantie ne s'applique qu'aux produits ayant fait l'objet d'un usage normal et conforme à leur destination, les clients ayant pris connaissance de tout document d'installation, de fonctionnement et d'entretien au préalable.

Les modalités d'application de la garantie (réparation, remplacement ou tout autre modalité) sont faites en fonction de l'appréciation par la SAS MD2i de la panne et du fournisseur du produit vendu.

Dans tous les cas, les frais de retour du produit, de main d'œuvre et de déplacement sont à la charge du client.
");

    // article 12
    $this->writeCGVtitle("PROPRIETE INTELLECTUELLE");
    $this->Write(3,
"Les marques, logos, slogans, graphismes, photographies, animations, vidéos et textes contenus sur le site www.mobaneo.com sont la propriété intellectuelle exclusive de la SAS MD2I ou de ses partenaires et ne peuvent être reproduits, utilisés ou représentés sans l'autorisation expresse de la SAS MD2I ou de ses partenaires, sous peine de poursuites judiciaires. 

Il en va ainsi notamment de l'utilisation de l'un de ces éléments à titre de metatags, liens hypertextes, noms de domaine etc.

Les droits d'utilisation concédés par la SAS MD2I au CLIENT PROFESSIONNEL sont strictement limités à l'accès, au téléchargement, à l'impression, à la reproduction sur tous supports (disque dur, disquette, CD-ROM, etc.) et à l'utilisation de ces documents pour un usage privé et personnel. Toute autre utilisation par les ABONNES est interdite sans l'autorisation de la SAS MD2I.

Chaque CLIENT PROFESSIONNEL s'interdit notamment de modifier, copier, reproduire, télécharger, diffuser, transmettre, exploiter commercialement et/ou distribuer de quelque façon que ce soit les services, les pages du site www.mobaneo.com, ou les codes informatiques des éléments composant les services et le site www.mobaneo.com.
");    
    
    // article 13
    $this->writeCGVtitle("PROTECTION DES DONNEES A CARACTERE PERSONNEL");
    $this->Write(3,
"La SAS MD2I s’engage à assurer la protection des données personnelles des CLIENTS PROFESSIONNELS dans les conditions ci-après définies.

Chaque CLIENT PROFESSIONNEL est seul responsable de la préservation de la confidentialité de son identifiant et de son mot de passe et est seul responsable de tous les accès réalisés par l’intermédiaire de son compte personnel, qu’ils soient autorisés ou non.

Toutes les données à caractère personnel recueillies par la SAS MD2I via le formulaire « ESPACE CLIENT » disponible sur le site www.mobaneo.com sont obligatoires pour devenir le CLIENT PROFESSIONNEL et sont  nécessaires pour bénéficier de l’ensemble des Produits et Services proposés par la SAS MD2I. Ces données collectées directement auprès du CLIENT PROFESSIONNEL sont traitées par la SAS MD2I uniquement pour  permettre la mise en œuvre et la gestion des Services proposés par la SAS MD2I et pour gérer les comptes personnels des CLIENTS PROFESSIONNELS.

La SAS MD2I déclare respecter scrupuleusement les législations françaises et européennes en matière de protection des données à caractère personnel et le site www.mobaneo.com a été déclaré auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL) sous le numéro 1425627.

Ces données sont conservées pendant toute la durée d’inscription du CLIENT PROFESSIONNEL et sont ensuite effacées et/ou conservées à titre d’archive aux fins d’établissement de la preuve d’un droit ou d’un contrat  qui peuvent être archivées conformément aux dispositions du Code de commerce relatives à la durée de conservation des livres et documents créés à l'occasion d'activités commerciales.

");    
    $this->AddPage();
    $this->Write(3,
"Les CLIENTS PROFESSIONNELS reconnaissent en outre être avisés de l'implantation d'un \"cookie\" dans leur ordinateur, destiné à enregistrer des informations relatives à la navigation sur le site www.mobaneo.com (informations fournies, pages consultées, date et heure de la consultation, etc.), en vue de faciliter la navigation sur le site en mémorisant certains paramètres. 

Conformément aux articles 38 et suivants de la loi n° 78-17 du 6 janvier 1978 modifiée relative à l'informatique, aux fichiers et aux libertés, toute personne peut obtenir communication et, le cas échéant, rectification ou suppression des informations la concernant, en s'adressant au service clientèle : commandes@techni-contact.com. Il est rappelé que toute personne peut, pour des motifs légitimes, s'opposer au traitement des  données la concernant.
");    
    
    // article 14
    $this->writeCGVtitle("FORCE MAJEURE");
    $this->Write(3,
"La SAS MD2I ne pourra être tenue pour responsable, ou considérée comme ayant failli aux présentes conditions, pour tout retard ou inexécution, lorsque la cause du retard ou de l'inexécution est liée à un cas de force  majeure telle qu'elle est définie par la jurisprudence des cours et tribunaux français y compris notamment en cas d'attaque de pirates informatiques, d'indisponibilité de matériels, fournitures, pièces détachées, équipements personnels ou autres ; et d'interruption, la suspension, la réduction ou les dérangements de l'électricité ou autres ou toutes interruptions de réseaux de communications électroniques.
");
    
    // article 15
    $this->writeCGVtitle("DISPOSITIONS GENERALES");
    $this->Write(3,
"Les présentes CGV constituent l’intégralité de l’accord conclu entre le  CLIENT PROFESSIONNEL et la SAS MD2I.

Aucune indication, aucun document ne pourra engendrer des obligations non comprises dans les présentes CGV, s'ils n'ont fait l'objet d'un nouvel accord entre les parties.

Le fait que l'une des PARTIES n'ait pas exigé l'application d'une clause quelconque des présentes CGV, que ce soit de façon permanente ou temporaire, ne pourra en aucun cas être considéré comme une renonciation à ladite clause.

En cas de difficulté d’interprétation entre l’un quelconque des titres figurant en tête des clauses, et l’une quelconque de celles-ci, les titres seront déclarés inexistants. 

Si l’une quelconque des stipulations des présentes CGV venait à être nulle au regard d’une disposition législative ou réglementaire en vigueur et/ou d’une décision de justice ayant autorité de la chose jugée, elle sera réputée non écrite mais n’affectera en rien la validité des autres clauses qui demeureront pleinement applicables.

En cas de différent survenant entre les PARTIES au sujet de l’interprétation, de l’exécution des présentes CGU, les PARTIES s’efforceront de le régler à l’amiable.
");    
    
    // article 16
    $this->writeCGVtitle("DROIT APPLICABLE");
    $this->Write(3,
"En cas de différend survenant entre les PARTIES au sujet de l’interprétation, de l’exécution ou de la résiliation des présentes CGV, les PARTIES s’efforceront de le régler à l’amiable. A défaut d’accord amiable dans un délai d’un (1) mois à compter de la saisine de l’une des PARTIES, le litige pourra être soumis aux tribunaux du ressort de la Cour d’Appel de Paris auxquels il est fait expressément attribution de compétence, nonobstant pluralité de défendeurs ou appel en garantie, y compris pour les procédures d’urgence ou les procédures conservatoires, en référé ou par requête.
");    
    
    $this->SetMargins(10,10,10);
    
  }
  
  var $titleTree = array();
  function writeCGVtitle($title, $rank=0) {
    switch ($rank) {
      case 0:
        $ti = key($this->titleTree)+1;
        $this->titleTree[$ti] = array();
        end($this->titleTree);
        
        $this->Cell(0,3,"",0,1);
        $this->SetFont('', 'B', 13);
        $this->Write(8,"ARTICLE ".$ti);
        $this->SetFont('');
        $this->Write(8.5," - ");
        //$this->y += 0.5;
        $this->SetFont('','B',7);
        $this->Write(9,$title);
        $curLineWidth = $this->LineWidth;
        $this->SetLineWidth(0.4);
        $this->Line(21,$this->y+5.7,$this->x+1,$this->y+5.7);
        $this->SetLineWidth($curLineWidth);
        $this->SetFont('');
        $this->Ln();
      break;
      case 1:
        $ti0 = key($this->titleTree);
        $ti1 = key($this->titleTree[$ti0])+1;
        $this->titleTree[$ti0][$ti1] = array();
        end($this->titleTree[$ti0]);
        
        $this->x += 10;
        $this->SetFont('','B');
        $this->MultiCell(0,3,$ti0.".".$ti1.". ".$title);
        $this->Ln();
        $this->SetFont('');
        
    }
  }
  
  function writeCGVarticleSubtitle($subtitle) {
  }
  
  function draw_ref_headers($cwl, $cxl) {
    $this->SetFont('Arial','B');
    $this->Cell($cwl['ref']  ,4,"Référence",1,0,'C');
    $this->Cell($cwl['label'],4,"Désignation",1,0,'L');
    $this->Cell($cwl['pu_ht'],4,"P.U. € HT",1,0,'C');
    $this->Cell($cwl['qty']  ,4,"Qté",1,0,'C');
    $this->Cell($cwl['disc'] ,4,"Rem",1,0,'C');
    $this->Cell($cwl['tt_ht'],4,"Total € HT",1,0,'C');
    $this->SetFont('Arial');
    $this->Ln();
  }
  
  function draw_ref_lines($t_y, $b_y, $cwl, $cxl) {
    $h = $b_y - $t_y;
    $this->Rect($cxl['ref']  ,$t_y,$cwl['ref']  ,$h);
    $this->Rect($cxl['label'],$t_y,$cwl['label'],$h);
    $this->Rect($cxl['pu_ht'],$t_y,$cwl['pu_ht'],$h);
    $this->Rect($cxl['qty']  ,$t_y,$cwl['qty']  ,$h);
    $this->Rect($cxl['disc'] ,$t_y,$cwl['disc'] ,$h);
    $this->Rect($cxl['tt_ht'],$t_y,$cwl['tt_ht'],$h);
  }
  
  /*function writeTopRects($cols) {
    
    $s = $this->source;
    $bx = $this->x;
    $by = $this->y;
    $lc_ro = 0; // last coll right offset
    
    $pdf->SetFont('Arial','B',9);
    $pdf->Cell(80,4," Numéro : ".$e['id'],1,0,'L');
    $pdf->Cell(55,4," Adresse livraison",1,0,'L');
    $pdf->Cell(55,4," Adresse facturation",1,0,'L');
    $pdf->Ln();
    
    $max_cell_height = 0;
    foreach ($cols as $col) {
      $cxo = $this->x = $bx+$lc_ro;
      $lc_ro += $col['width'];
      $header_h = isset($col['header-height']) ? $col['header-height'] : 4;
      $cell_tm = isset($col['cell-top-margin']) ? $col['cell-top-margin'] : 0.5;
      $cell_bm = isset($col['cell-bottom-margin']) ? $col['cell-bottom-margin'] : 5;
      $cell_mh = isset($col['cell-min-height']) ? $col['cell-min-height'] : 30;
      $lh = isset($col['line-height']) ? $col['line-height'] : 3.5;
      $this->y = $by;
      $this->SetFont('Arial','B',9);
      if (is_string($col['content'])) {
        switch ($col['content']) {
          case "delivery_address":
            $this->Cell($col['width'],$header_h," Adresse livraison",1,0,'L');
            $this->x = $cxo;
            $this->y = $by + $header_h + $cell_tm;
            $this->SetFont('Arial');
            $this->MultiCell($col['width'],$lh,
              " ".$s['societe2']."\n".
              " ".$s['prenom2']." ".$s['nom2']."\n".
              " ".$s['adresse2']."\n".
              " ".$s['cadresse2']."\n".
              " ".$s['cp2']." – ".$s['ville2']."\n".
              " ".$s['pays2']."\n".
              " Tel : ".$s['tel2']."\n",0,"L");
            break;
          case "billing_address":
            $this->Cell($col['width'],4," Adresse facturation",1,0,'L');
            $this->x = $cxo;
            $this->y = $by + $header_h + $cell_tm;
            $this->SetFont('Arial');
            $pdf->MultiCell($col['width'],$lh,
              " ".$s['societe']."\n".
              " ".$s['prenom']." ".$s['nom']."\n".
              " ".$s['adresse']."\n".
              " ".$s['cadresse']."\n".
              " ".$s['cp']." – ".$s['ville']."\n".
              " ".$s['pays']."\n".
              " Tel : ".$s['tel']."\n",0,"L");
            break;
        }
      } elseif (is_array($col['content'])) {
        foreach ($col['content'] as $ccl) { // col content line
          if (is_array($ccl)) {
            foreach ($ccl as $cclo => $cclov) { // col content line option (value)
              switch ($cclo) {
                case 'line-height': $lh = $cclov; break;
              }
            }
          } elseif (is_scalar($ccl)) {
            $lp = preg_match_all('`(.*?)<([^>]+)>(.*?)</\2>`', $a, $m, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
            if (empty($lp))
              
          }
        }
      }
      $max_cell_height = max($max_cell_height, $this->y+$cell_bm);
    }
    
    $this->Rect($cxo,$by+$header_h,$col['width'],30);
    
    $col1 = array(
      'width' => 80,
      'content' => array(
        array('line-height' => 4),
        " Affaire suivie par : ".$e['created_user']['name'],
        " Tel : ".$e['created_user']['phone'],
        " Email : ".$e['created_user']['email'],
        "",
        array('line-height' => 3.5),
        " Validité : <b>".$e['validity']."</b>",
        " Mode de règlement : <b>".utf8_decode(Estimate::getPaymentModeText($e['payment_mode']))."</b>"
      )
    );
    $col2 = array('width' => 55, 'content' => "delivery_address");
    $col3 = array('width' => 55, 'content' => "billing_address");
  }
  
  function writeCenterMsg() {
  }
  
  function writeReferences() {
  }
  
  function writeTotals() {
  }*/
  
  
}
