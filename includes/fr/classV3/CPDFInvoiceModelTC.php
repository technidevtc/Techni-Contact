<?php

require_once(INCLUDES_PATH.'fpdf/fpdf.php');

class PDFInvoiceModelTC extends FPDF {

  function Header() {
    $x = $this->x = 20;
    $y = $this->y = 10; // make sure to have 0.1 rounded values
    $lMargin = $this->lMargin;
    $tMargin = $this->tMargin;
    $rMargin = $this->rMargin;
    $this->SetMargins($x,$y,$x);
    $this->Image(SECURE_PATH.'ressources/images/logo_TC.png',120,10,80);
    $this->SetFont('Arial','B',10);
    $this->SetY($y+2);
    $this->Cell(80,4,"Techni-Contact / M.D2i",0,1);
    $this->SetFont('','');
    $this->MultiCell(80,4,
      "253 rue Gallieni\n".
      "F-92774 BOULOGNE BILLANCOURT cedex\n".
      "S.A.S. au capital de 160 000 �\n".
      "RCS Nanterre B 392 772 497\n".
      "Tva Intra. : FR12 392 772 497\n",0);
    $this->Cell(80,4,"http://www.techni-contact.com/",0,0);
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
LA SOCIETE MD2I, Soci�t� par actions simplifi�e (SAS) au capital de 160.000,00 euros, immatricul�e au Registre du Commerce et des Soci�t�s de NANTERRE sous le num�ro B 392 772 497, sise 253, rue Gallieni, 92100 BOULOGNE BILLANCOURT.

Repr�sent�e par son Pr�sident Directeur G�n�ral Monsieur Fr�d�ric STUMM

Ci-apr�s d�nomm�e � SAS MD2I�

");
    $this->SetFont('','B');
    $this->Cell(0,3,"ET :",0,1);
    $this->SetFont('');
    $this->Write(3,
"
Le client professionnel ou la collectivit� locale

Ci apr�s d�nomm�e � LE CLIENT PROFESSIONNEL �

La SAS MD2I et LE CLIENT PROFESSIONNEL sont ci-apr�s d�sign�s collectivement, les � PARTIES �, et individuellement une � PARTIE �,


");
    $this->SetFont('','B');
    $this->Cell(0,3,"IL A PREALABLEMENT ETE EXPOSE CE QUI SUIT :",0,1,'C');
    $this->SetFont('');
    $this->Write(3,
"

La SAS MD2I est la soci�t� �ditrice et exploitante du site www.techni-contact.com.

Ce site est une galerie marchande destin�e aux professionnels. A ce titre, elle propose � la vente des mat�riels, fournitures, �quipements dont les professionnels et collectivit�s ont besoin dans le cadre de leur activit�.

Une partie du catalogue disponible sur le site est propos�e par des PARTENAIRES ANNONCEURS. Les demandes de devis concernant ces produits leur sont directement adress�es, la SAS MD2I ne servant que d�interm�diaire � la mise en relation de ces derniers.

En souscrivant aux pr�sentes Conditions G�n�rales de Vente, LE CLIENT PROFESSIONNEL d�clare que l�achat qu�il effectue sur la PLATEFORME r�pond directement � un besoin professionnel et qu�il commande le produit ou service directement en sa qualit� de professionnel ; reconnaissant ainsi qu�il ne saurait en aucun cas �tre consid�r� comme un consommateur ou un non professionnel au sens de la Loi fran�aise.
");
    
    $this->AddPage();
    $this->Rect(20,$this->y,170,15);
    $this->SetFont('','B');
    $this->Cell(0,3,"Important",0,1,'C');
    $this->SetFont('');
    $this->Write(3,
"Toute commande effectu�e sur le site www.techni-contact.com implique obligatoirement l'acceptation sans r�serve, par LE CLIENT PROFESSIONNEL, des pr�sentes Conditions G�n�rales de Vente.
Seules les pr�sentes constituent les Conditions G�n�rales de Vente. Elles sont seules applicables � la relation contractuelle.
Elles remplacent et annulent toutes autres conditions ant�rieures sauf d�rogation expresse et �crite de la Soci�t� MD2I.
");
    $this->Ln();
    $this->SetFont('','',13);
    $this->Cell(0,3,"*",0,1,'C');
    $this->Cell(0,3,"*        *",0,1,'C');
    $this->Ln();
    $this->Ln();
    $this->SetFont('','B',7);
    $this->Cell(0,3,"C�EST DANS CES CONDITIONS QUE LES PARTIES ONT ARRETE ET CONVENU CE QUI SUIT :",0,1,'C');
    $this->Ln();
    
    // article 1
    $this->writeCGVtitle("DEFINITIONS");
    $this->Write(3,
"Les termes, mentionn�s ci-dessous, ont dans les pr�sentes CONDITIONS GENERALES DE VENTE, la signification suivante :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� BOUTIQUE EN LIGNE �");
    $this->SetFont('');
    $this->Write(3,
" : Site internet marchand de la SAS MD2I accessible depuis l�adresse http://www.techni-contact.com.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� CONDITIONS GENERALES DE VENTE �");
    $this->SetFont('');
    $this->Write(3," ou ");
    $this->SetFont('','B');
    $this->Write(3,"� CGV �");
    $this->SetFont('');
    $this->Write(3,
" : d�signe le pr�sent contrat r�put� accept� par les PARTIES.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� LE CLIENT PROFESSIONNEL �");
    $this->SetFont('');
    $this->Write(3,
" : toute personne physique majeure et capable ou toute personne morale agissant par l�interm�diaire d�une personne physique certifiant disposer de la capacit� juridique pour contracter au nom et pour le compte de la personne morale, contractant avec la SAS MD2I.

");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� COMMANDE VALIDEE �");
    $this->SetFont('');
    $this->Write(3,
" : une commande ne devient une Commande Valid�e qu�� partir de la r�ception, par la SAS MD2I, du paiement effectif de la somme due (prix principal, int�r�ts et accessoires) par le Client tant pour les Produits ou Services Command�s qu�au titre de la Livraison.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� PARTENAIRE ANNONCEUR �");
    $this->SetFont('');
    $this->Write(3,
" : Soci�t� proposant une offre de produit ou service sur la PLATEFORME. Le PARTENAIRE ANNONCEUR est seul et enti�rement responsable de l�ex�cution de la commande pass�e par un CLIENT PROFESSIONNEL pour les PRODUITS PARTENAIRES.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� PRODUIT COMMANDE �");
    $this->SetFont('');
    $this->Write(3,
" : s�entend du Produit ou du Service d�sign� par le CLIENT PROFESSIONNEL lors du processus de commande ou de tout autre produit similaire de m�me valeur et ayant des caract�ristiques substantielles identiques.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� PRODUIT PARTENAIRE �");
    $this->SetFont('');
    $this->Write(3,
" : s�entend du Produit ou Service qui est propos� et mis en ligne par les partenaires annonceurs de la soci�t� MD2I.
");
    $this->SetFont('','B');
    $this->text($this->x-5, $this->y+2.5,"�");
    $this->Write(3,"� LIVRAISON �");
    $this->SetFont('');
    $this->Write(3,
" : La livraison est consid�r�e comme r�alis�e d�s la date de la premi�re pr�sentation des PRODUITS OU SERVICES COMMANDES � l�adresse de livraison mentionn�e lors de la commande par le Client.
");
    $this->SetLeftMargin(20);
    
    // article 2
    $this->writeCGVtitle("OBJET");
    $this->Write(3,
"Les pr�sentes CGV ont pour objet de fixer les dispositions contractuelles relatives aux droits et obligations respectifs des PARTIES dans le cadre de leurs relations contractuelles.
");
    
    // article 3
    $this->writeCGVtitle("MODIFICATIONS DES PRESENTES CONDITIONS GENERALES DE VENTE");
    $this->Write(3,
"La SAS MD2I pourra modifier � tout moment les pr�sentes CGV. LE CLIENT PROFESSIONNEL sera inform� de la nature de ces modifications d�s leur mise en ligne sur le site www.techni-contact.com. 

Les modifications entreront en vigueur (XX) mois/jours apr�s leur mise en ligne sur le site. 

Seules les CGV publi�es le jour de la commande sont applicables. Elles sont port�es � la connaissance du client lors du processus de commande et avant le paiement.

La SAS MD2I se r�serve le droit d�appliquer des conditions g�n�rales de vente particuli�res lorsque cela lui appara�t n�cessaire. Il en est NOTAMMENT ainsi lorsque :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande pr�sente un risque financier excessif (�tat de cessation de paiement du CLIENT PROFESSIONNEL, r�f�rences commerciales jug�es insuffisantes par la SAS MD2I, CLIENT PROFESSIONNEL nouveau ou irr�gulier dans ces commandes);
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande provient d�un CLIENT PROFESSIONNEL n�ayant pas acquitt� l�ensemble de ses obligations n�es d�affaires ant�rieures ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"La commande provient d�un CLIENT PROFESSIONNEL ayant manifest� un comportement d�loyal ou contraire aux usages commerciaux.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Dans le cas d�un risque financier excessif pr�sent� par LE CLIENT PROFESSIONNEL, la SAS MD2I pourra exig�e des garanties telles que l�Administrateur judiciaire et/ou des modalit�s de paiement particuli�res (paiement avant le d�part des marchandises de la SAS MD2I, paiement � la livraison�). 
");
    
    $this->AddPage();
    
    // article 4
    $this->writeCGVtitle("PRODUIT OU SERVICE COMMANDE");
    $this->writeCGVtitle("Les produits en vente directe",1);
    $this->Write(3,
"Tous les PRODUITS COMMANDES sont d�crits par la SAS MD2I aussi pr�cis�ment que possible en fonction des seules informations communiqu�es par le fournisseur.

Les caract�ristiques figurant sur les catalogues, prospectus, fiches techniques ou tout autre document sont donn�es � titre indicatif et ne sauraient en aucun cas engag�es la responsabilit� de la SAS MD2I.

En cas de doute sur les caract�ristiques d�un PRODUIT COMMANDE, le Client doit contacter la SAS MD2I au 0.811.06.07.07 (co�t d�un appel local non surtax�).

Les photographies des PRODUITS COMMANDES sont communiqu�es � titre illustratif et ne peuvent par cons�quent engager la responsabilit� de la SAS MD2I, ni remettre en cause une COMMANDE VALIDEE, que s�il existe d��ventuelles diff�rences portant sur des qualit�s substantielles du produit. 

Une diff�rence portant sur la couleur, les fonctionnalit�s secondaires ou les accessoires d�un produit ne peut en aucun engager la responsabilit� de la SAS MD2I, ni remettre en cause une COMMANDE VALIDEE.

Les PRODUITS COMMANDES et les offres promotionnelles propos�s par la SAS MD2I ne sont valables que dans la limite des stocks disponibles.

");
    $this->writeCGVtitle("Les PRODUITS PARTENAIRES",1);
    $this->Write(3,
"Les annonces des PRODUITS PARTENAIRES sont r�alis�es par les partenaires annonceurs de la SAS MD2I qui leur met � disposition un espace publicitaire. La SAS MD2I intervient uniquement dans la mise en relation du CLIENT PROFESSIONNEL et des partenaires annonceurs. Elle ne concourt � aucun moment dans la relation contractuelle qui peut s��tablir entre le CLIENT PROFESSIONNEL et le partenaire annonceur.
");
    
    // article 5
    $this->writeCGVtitle("CONFIRMATION DE LA COMMANDE");
    $this->Write(3,
"Une fois le PRODUIT COMMANDE s�lectionn�, LE CLIENT PROFESSIONNEL �met un bon de commande � la SAS MD2I soumis � l�acceptation de celle-ci.

Ce bon de commande n�engage la SAS MD2I que si celui-ci est confirm� par elle.

LE CLIENT PROFESSIONNEL recevra la confirmation de la SAS MD2I de l�enregistrement de son bon de commande par courrier �lectronique � l�adresse mail qu�il aura communiqu� pour les besoins de sa commande.
");
    
    // article 6
    $this->writeCGVtitle("TRANSFERT DE PROPRIETE");
    $this->Write(3,
"Le transfert de propri�t� ne s�effectue qu�une fois la COMMANDE VALIDEE, c�est-�-dire au complet paiement du prix (prix principal, int�r�ts et accessoires) par le CLIENT PROFESSIONNEL. Dans l�attente du paiement effectif du prix, la SAS MD2I se r�serve la propri�t� du PRODUIT COMMANDE.

Le transfert de propri�t� a lieu, en cas de paiement par ch�que ou effet de commerce, qu�au moment de l�encaissement effectif de ces derniers.
");    
    
    // article 7
    $this->writeCGVtitle("LES CONDITIONS FINANCIERES");
    $this->writeCGVtitle("Le prix",1);
    $this->Write(3,
"Le prix de vente du PRODUIT COMMANDE est indiqu� en devise euro (�) hors taxes (� HT) hors co�t de livraison pour un produit command� pris dans les locaux de la SAS MD2I, non emball�.

Le prix de livraison du PRODUIT COMMANDE est indiqu� en devise euro (�) hors taxes (� HT)

Ils sont indiqu�s au CLIENT PROFESSIONNEL au moment de l��laboration de son bon de commande et ce avant la phase de paiement. 

Il est entendu entre les PARTIES que le CLIENT PROFESSIONNEL prendra � sa charge, en sus de ce qui apparaitra dans le bon de commande :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Le prix du montage et de la mise en route du mat�riel si besoin est ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Le prix de l�emballage ex�cut� conform�ment aux usages afin de garantir le transport du PRODUIT COMMANDE dans les meilleures conditions ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"Les taxes, droits, frais, timbres et primes d�assurance.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Les prix des PRODUITS COMMANDES peuvent �voluer � tout moment en fonction de la politique tarifaire. Les PRODUITS COMMANDES sont factur�s au prix en vigueur lors de l'enregistrement de la commande.

Les prix des PRODUITS COMMANDES et le prix de livraison ne sont valables qu'en France m�tropolitaine.

Les frais engendr�s par une livraison � l��tranger sont � la charge du CLIENT PROFESSIONNEL (imp�ts, taxes, redevances, frais de contr�le technique, frais �ventuellement dus � l�application d�une l�gislation �trang�re�).
");
    $this->AddPage();
    $this->writeCGVtitle("Le r�glement",1);
    $this->Write(3,
"Toutes les commandes effectu�es sur la BOUTIQUE EN LIGNE sont payables � compter du jour de la commande.

Le d�lai de validation de la commande est donc tributaire du mode de paiement choisi qui s�effectue directement aupr�s de la SAS MD2I, soit :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"un jour ouvr� pour le paiement par carte bancaire (visa/mastercard),
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"2 jours ouvr�s pour le virement bancaire et le paiement via un compte PAYPAL, 
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"ainsi que de 3 � 4 jours ouvr�s � compter de la r�ception du ch�que bancaire ou du mandat administratif.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"Les traites qui sont �ventuellement jointes aux factures pour acceptation doivent �tre retourn�es � la SAS MD2I dans les quarante-huit (48) heures.

");
    $this->writeCGVtitle("D�faut de paiement",1);
    $this->Write(3,
"Tout retard de paiement du prix entra�ne l�application de p�nalit�s de retard d�un taux d�int�r�t �gal � une fois et demie le taux d�int�r�t l�gal pour tout paiement intervenu plus d�un jour apr�s la facturation de la commande par la SAS MD2I sauf autorisation pr�alable, expresse et �crite de celle-ci.

Dans le cas o� le paiement se r�v�lerait �tre irr�gulier, incomplet ou inexistant, en raison d�une faute qui est imputable au CLIENT PROFESSIONNEL, le bon de commande serait annul�, les frais en d�coulant �tant � la charge du CLIENT PROFESSIONNE, une action civile et/ou p�nale pouvant, le cas �ch�ant, �tre entreprise � son encontre.
");

    // article 8
    $this->writeCGVtitle("TRANSFERT DES RISQUES");
    $this->writeCGVtitle("En France",1);
    $this->Write(3,
"Le CLIENT PROFESSIONNEL supporte les risques pesant sur le/les PRODUIT(S) COMMANDE(S) d�s :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"L��change des consentements entre les PARTIES, c�est-�-dire l�acceptation par la SAS MD2I du bon de commande �mis par le CLIENT PROFESSIONNEL ;
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"la mise � disposition dans les locaux de MD2I du/des PRODUITS PARTENAIRES avant tout chargement.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->writeCGVtitle("A l��tranger",1);
    $this->Write(3,
"Le transfert des risques s�effectuera conform�ment � l�incoterm figurant sur la confirmation de la commande.
");
    
    // article 9
    $this->writeCGVtitle("LA LIVRAISON");
    $this->writeCGVtitle("Les modalit�s de la LIVRAISON",1);
    $this->Write(3,
"Les PRODUITS COMMANDES sur la Boutique en Ligne peuvent �tre livr�s en France M�tropolitaine ou � l��tranger sous certaines conditions vis�es supra. 

Les LIVRAISONS s'effectuent du lundi au vendredi. La SAS MD2I s�engage � respecter les d�lais de LIVRAISON indiqu�e pour les PRODUITS COMMANDES en vente directe sur le site www.techni-contact.com, except� les mois de Juillet et d�Ao�t ou ces derniers pourront �tre rallong�s sans pr�avis. 

Le d�lai de LIVRAISON commence � courir une fois la COMMANDE VALIDEE. 

La remise de la marchandise sera effectu�e contre signature par le CLIENT PROFESSIONNEL r�ceptionnaire d'un bon de livraison. Le CLIENT PROFESSIONNEL a pour obligation de v�rifier la conformit� de la marchandise livr�e au moment de la LIVRAISON, avant de signer le bon de livraison.

Toute anomalie concernant la LIVRAISON (avarie, produit manquant par rapport au bon de livraison, colis endommag�, produits cass�s...) devra �tre imp�rativement indiqu�e sur le bon de livraison sous forme de \"r�serves manuscrites\", accompagn�e de la signature du CLIENT PROFESSIONNEL.

");
    $this->writeCGVtitle("Les complications li�es � la LIVRAISON",1);
    $this->Write(3,
"Toute modification faite par LE CLIENT PROFESSIONNEL d�un ordre de livraison en cours, soumise au pr�alable � l�acceptation de la SAS MD2I, entraine une prorogation de ce d�lai pr�vue selon les modalit�s d�finis par elle.

Si la configuration r�elle du lieu de livraison emp�che physiquement le d�chargement du ou des articles, le Vendeur se r�serve le droit d'annuler la livraison et de proc�der au remboursement de l'article, d�duction faite du co�t de livraison et du co�t de retour.

En cas de vente � l�exportation, les diff�rentes autorisations (licence d�importation, autorisation de transfert de devises), devront avoir �t� obtenues pr�alablement par le client.

Lorsque le client refuse de r�ceptionner, la SAS MD2I est en droit de mettre le/les PRODUIT(S) COMMANDE(S) en entrep�t aux frais du client, comprenant �galement les frais de transport et de manutention. 

Si le CLIENT PROFESSIONNEL ne proc�de pas au retrait de ce/ces PRODUIT(S) COMMANDE(S) quinze (15) jours apr�s sa/leur mise(s) � disposition, la SAS MD2I pourra r�silier unilat�ralement le contrat et proc�der � la revente des marchandises. La SAS MD2I pourra exiger du CLIENT PROFESSIONNEL de payer la diff�rence de prix initial convenu entre les PARTIES et le prix de revente qu�� obtenu la SAS MD2I.

Il en va de m�me, lorsque le CLIENT PROFESSIONNEL est tenu directement de proc�der au retrait du/des PRODUIT(S) COMMANDE(S) dans les entrep�ts de la SAS MD2I.
");

    $this->AddPage();
    
    // article 10
    $this->writeCGVtitle("RESPONSABILITE");
    $this->writeCGVtitle("La LIVRAISON",1);
    $this->Write(3,
"La responsabilit� de la SAS MD2I, ne pourra pas �tre retenue en cas :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"de retard ou difficult�s de livraison dus aux gr�ves, manque de camion ou wagon, incendie, dans l�approvisionnement, dans la fabrication du PRODUIT COMMANDE,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"du non-paiement du CLIENT PROFESSIONNEL,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"de force majeure ou fortuite, �v�nements de guerre ou troubles int�rieurs.
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"La responsabilit� de la SAS MD2I ne pourra en aucun cas �tre engag�e en cas d�inex�cution ou de mauvaise ex�cution des prestations contractuelles imputable au CLIENT PROFESSIONNEL notamment lors de la saisie de la commande ; ou � tout autre cas de force majeure.

Dans tous les cas, la SAS MD2I n�est tenue qu�� une simple obligation de moyen. Toute mise en cause de sa responsabilit� n�cessite la preuve d�une faute commise par elle.

");
    $this->writeCGVtitle("Les vices-cach�s",1);
    $this->Write(3,
"La SAS MD2I d�cline toute responsabilit� qui r�sulterait d�un accident de personnes ou de biens provenant d�un vice-cach� d�un PRODUIT COMMANDE

De m�me, elle ne saurait verser une quelconque indemnit� � titre de dommages et int�r�ts exig� par un CLIENT PROFESSIONNEL dont le PRODUIT COMMANDE contient un vice cach�.

");
    $this->writeCGVtitle("Les Partenaires annonceurs",1);
    $this->Write(3,
"La SAS MD2I propose sur son site www.techni-contact.com des produits partenaires. En effet, la SAS MD2I met � disposition de ses Partenaires-annonceurs des espaces publicitaires priv�s o� ces derniers peuvent mettre en ligne leur produit.

La soci�t� MD2I n�interf�re � aucun moment dans la vente du PRODUIT PARTENAIRE, elle joue le r�le d�interm�diation entre le CLIENT PROFESSIONNEL et le Partenaire-annonceur.

Dans ces conditions, elle ne saurait voir engager sa responsabilit� en cas de probl�me r�sultant de cette relation contractuelle entre le CLIENT PROFESSIONNEL et le Partenaire-annonceur.
");

    // article 11
    $this->writeCGVtitle("GARANTIES");
    $this->Write(3,
"Le d�lai de garantie contre les vices-cach�s est d�un an, � compter de la date de facturation et sous condition que le vice en cause ne figure pas dans la liste ci-dessous :
");
    $this->SetLeftMargin(35);
    $this->Ln();
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"lorsque le vice provient de la mati�re fournie par le client, d�une conception qui a impos�e, ou des conditions de stockage inadapt�es,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"les incidents survenus dans tous les cas de force majeure ou fortuite, �v�nements de guerre ou troubles int�rieurs,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"lorsque le vice provient de la simple n�gligence du CLIENT PROFESSIONNEL, de l�usure normale des fournitures, du d�faut d�entretien du PRODUIT COMMANDE ou encore de l�utilisation ou de la mise en route de celui-ci par le CLIENT PROFESSIONNE non conform�ment aux instructions de la SAS MD2I ou suivant les r�gles de l�art de la profession,
");
    $this->text($this->x-5, $this->y+2,"-");
    $this->Write(3,
"lorsque des d�fectuosit�s proviennent d�une surcharge du mat�riel ou lorsqu�une pi�ce ou une partie d�appareil d�fectueuse entra�ne des d�g�ts sur l�ensemble de l�appareil ou �quipements environnants livr�s par la SAS MD2I
");
    $this->SetLeftMargin(20);
    $this->Ln();
    $this->Write(3,
"LE CLIENT PROFESSIONNEL devra justifier de la mat�rialit� de ces vices par �crit et dans le d�lai d�un an � compter de la facturation sous lettre recommand�e avec avis de r�ception adress�e � la SAS MD2I.
La SAS MD2I proc�dera aux mesures n�cessaires afin de rem�dier � ce vice-cach�, c�est-�-dire, la remise en �tat ou le remplacement de toutes pi�ces reconnues d�fectueuses.
");

    // article 12
    $this->writeCGVtitle("PROPRIETE INTELLECTUELLE");
    $this->Write(3,
"Les marques, logos, slogans, graphismes, photographies, animations, vid�os et textes contenus sur le site www.techni-contact.com sont la propri�t� intellectuelle exclusive de la SAS MD2I ou de ses partenaires et ne peuvent �tre reproduits, utilis�s ou repr�sent�s sans l'autorisation expresse de la SAS MD2I ou de ses partenaires, sous peine de poursuites judiciaires. 

Il en va ainsi notamment de l'utilisation de l'un de ces �l�ments � titre de metatags, liens hypertextes, noms de domaine etc.

Les droits d'utilisation conc�d�s par la SAS MD2I au CLIENT PROFESSIONNEL sont strictement limit�s � l'acc�s, au t�l�chargement, � l'impression, � la reproduction sur tous supports (disque dur, disquette, CD-ROM, etc.) et � l'utilisation de ces documents pour un usage priv� et personnel. Toute autre utilisation par les ABONNES est interdite sans l'autorisation de la SAS MD2I.

Chaque CLIENT PROFESSIONNEL s'interdit notamment de modifier, copier, reproduire, t�l�charger, diffuser, transmettre, exploiter commercialement et/ou distribuer de quelque fa�on que ce soit les services, les pages du site www.techni-contact.com, ou les codes informatiques des �l�ments composant les services et le site www.techni-contact.com.
");    
    
    $this->AddPage();
    
    // article 13
    $this->writeCGVtitle("PROTECTION DES DONNEES A CARACTERE PERSONNEL");
    $this->Write(3,
"La SAS MD2I s�engage � assurer la protection des donn�es personnelles des CLIENTS PROFESSIONNELS dans les conditions ci-apr�s d�finies.
Chaque CLIENT PROFESSIONNEL est seul responsable de la pr�servation de la confidentialit� de son identifiant et de son mot de passe et est seul responsable de tous les acc�s r�alis�s par l�interm�diaire de son compte personnel, qu�ils soient autoris�s ou non.
Toutes les donn�es � caract�re personnel recueillies par la SAS MD2I via le formulaire � ESPACE CLIENT � disponible sur le site www.techni-contact.com sont obligatoires pour devenir le CLIENT PROFESSIONNEL et sont n�cessaires pour b�n�ficier de l�ensemble des Produits et Services propos�s par la SAS MD2I. Ces donn�es collect�es directement aupr�s du CLIENT PROFESSIONNEL sont trait�es par la SAS MD2I uniquement pour permettre la mise en �uvre et la gestion des Services propos�s par la SAS MD2I et pour g�rer les comptes personnels des CLIENTS PROFESSIONNELS.

La SAS MD2I d�clare respecter scrupuleusement les l�gislations fran�aises et europ�ennes en mati�re de protection des donn�es � caract�re personnel et le site www.techni-contact.com a �t� d�clar� aupr�s de la Commission Nationale de l'Informatique et des Libert�s (CNIL) sous le num�ro XXXX.

Ces donn�es sont conserv�es pendant toute la dur�e d�inscription du CLIENT PROFESSIONNEL et sont ensuite effac�es et/ou conserv�es � titre d�archive aux fins d��tablissement de la preuve d�un droit ou d�un contrat qui peuvent �tre archiv�es conform�ment aux dispositions du Code de commerce relatives � la dur�e de conservation des livres et documents cr��s � l'occasion d'activit�s commerciales.

Les CLIENTS PROFESSIONNELS reconnaissent en outre �tre avis�s de l'implantation d'un \"cookie\" dans leur ordinateur, destin� � enregistrer des informations relatives � la navigation sur le site www.techni-contact.com (informations fournies, pages consult�es, date et heure de la consultation, etc.), en vue de faciliter la navigation sur le site en m�morisant certains param�tres. 
Conform�ment aux articles 38 et suivants de la loi n� 78-17 du 6 janvier 1978 modifi�e relative � l'informatique, aux fichiers et aux libert�s, toute personne peut obtenir communication et, le cas �ch�ant, rectification ou suppression des informations la concernant, en s'adressant au service client�le : contact@appart-maison.fr. Il est rappel� que toute personne peut, pour des motifs l�gitimes, s'opposer au traitement des donn�es la concernant.
");    
    
    // article 14
    $this->writeCGVtitle("FORCE MAJEURE");
    $this->Write(3,
"La SAS MD2I ne pourra �tre tenue pour responsable, ou consid�r�e comme ayant failli aux pr�sentes conditions, pour tout retard ou inex�cution, lorsque la cause du retard ou de l'inex�cution est li�e � un cas de force majeure telle qu'elle est d�finie par la jurisprudence des cours et tribunaux fran�ais y compris notamment en cas d'attaque de pirates informatiques, d'indisponibilit� de mat�riels, fournitures, pi�ces d�tach�es, �quipements personnels ou autres ; et d'interruption, la suspension, la r�duction ou les d�rangements de l'�lectricit� ou autres ou toutes interruptions de r�seaux de communications �lectroniques.
");
    
    // article 15
    $this->writeCGVtitle("DISPOSITIONS GENERALES");
    $this->Write(3,
"Les pr�sentes CGV constituent l�int�gralit� de l�accord conclu entre l�ABONNE et la SAS MD2I.

Aucune indication, aucun document ne pourra engendrer des obligations non comprises dans les pr�sentes CGV, s'ils n'ont fait l'objet d'un nouvel accord entre les parties.

Le fait que l'une des PARTIES n'ait pas exig� l'application d'une clause quelconque des pr�sentes CGV, que ce soit de fa�on permanente ou temporaire, ne pourra en aucun cas �tre consid�r� comme une renonciation � ladite clause.

En cas de difficult� d�interpr�tation entre l�un quelconque des titres figurant en t�te des clauses, et l�une quelconque de celles-ci, les titres seront d�clar�s inexistants. 

Si l�une quelconque des stipulations des pr�sentes CGV venait � �tre nulle au regard d�une disposition l�gislative ou r�glementaire en vigueur et/ou d�une d�cision de justice ayant autorit� de la chose jug�e, elle sera r�put�e non �crite mais n�affectera en rien la validit� des autres clauses qui demeureront pleinement applicables.

En cas de diff�rent survenant entre les PARTIES au sujet de l�interpr�tation, de l�ex�cution des pr�sentes CGU, les PARTIES s�efforceront de le r�gler � l�amiable.
");    
    
    // article 16
    $this->writeCGVtitle("DROIT APPLICABLE");
    $this->Write(3,
"En cas de diff�rend survenant entre les PARTIES au sujet de l�interpr�tation, de l�ex�cution ou de la r�siliation des pr�sentes CGV, les PARTIES s�efforceront de le r�gler � l�amiable. A d�faut d�accord amiable dans un d�lai d�un (1) mois � compter de la saisine de l�une des PARTIES, le litige pourra �tre soumis aux tribunaux du ressort de la Cour d�Appel de Paris auxquels il est fait express�ment attribution de comp�tence, nonobstant pluralit� de d�fendeurs ou appel en garantie, y compris pour les proc�dures d�urgence ou les proc�dures conservatoires, en r�f�r� ou par requ�te.
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
    $this->Cell($cwl['ref']  ,4,"R�f�rence",1,0,'C');
    $this->Cell($cwl['label'],4,"D�signation",1,0,'L');
    $this->Cell($cwl['pu_ht'],4,"P.U. � HT",1,0,'C');
    $this->Cell($cwl['qty']  ,4,"Qt�",1,0,'C');
    $this->Cell($cwl['disc'] ,4,"Rem",1,0,'C');
    $this->Cell($cwl['tt_ht'],4,"Total � HT",1,0,'C');
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
    $pdf->Cell(80,4," Num�ro : ".$e['id'],1,0,'L');
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
              " ".$s['cp2']." � ".$s['ville2']."\n".
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
              " ".$s['cp']." � ".$s['ville']."\n".
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
        " Validit� : <b>".$e['validity']."</b>",
        " Mode de r�glement : <b>".utf8_decode(Estimate::getPaymentModeText($e['payment_mode']))."</b>"
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
