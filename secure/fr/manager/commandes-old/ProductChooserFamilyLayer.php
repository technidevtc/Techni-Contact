<style>
input.qtte { font: bold 11px arial, helvetica, sans-serif; color: #f40309; width: 23px; height: 14px; padding: 2px 2px; margin: 0; text-align: right; border: #d5d5d6 1px solid; }
img.qtte  { width: 14px; height: 10px; border: 0; cursor: pointer; float: right; clear: both; }
</style>
<script>
  function setQtte(tag, cible){
    if(tag.value != '' && tag.value <= 999999999 && tag.value >= 0){
      var valueCible = $('#'+cible).val();
      $('#'+cible).val(valueCible != '' ? valueCible : 1);
    }else
      $('#'+cible).val('');
  }
</script>
<div class="PELayer" id="ProductChooserFamilyLayer">
  <div style="width: 300px; margin: 10px" >

    Veuillez rentrer les ID TC à intégrer à la commande, ainsi que leur(s) quantité(s)<br />
    <br />
    <div  style="width: 240px" id="listInputQtte">
      <label for="product1">Produit 1 : </label><input type="text" value="" onBlur="setQtte(this, 'qteProduct1');" class="referenceContentID" id="product1" />
        <img src="../ressources/quantite_plus.gif"  class="qtte" width="14" height="10" border="0" alt="Ajouter" onclick="set_qte('Product1','+1')" />
        <img src="../ressources/quantite_moins.gif" class="qtte" width="14" height="10" border="0" alt="Retirer" onclick="set_qte('Product1','-1')" />
        <input type="text" id="qteProduct1" class="qtte" onblur="set_qte('Product1', this.value)" value="" /><br />
      <label for="product2">Produit 2 : </label><input type="text" value="" onBlur="setQtte(this, 'qteProduct2');" class="referenceContentID" id="product2" />
        <img src="../ressources/quantite_plus.gif" class="qtte" width="14" height="10" border="0" alt="Ajouter" onclick="set_qte('Product2','+1')" />
        <img src="../ressources/quantite_moins.gif" class="qtte" width="14" height="10" border="0" alt="Retirer" onclick="set_qte('Product2','-1')" />
        <input type="text" id="qteProduct2" class="qtte" onblur="set_qte('Product2', this.value)" value="" /><br />
      <label for="product3">Produit 3 : </label><input type="text" value="" onBlur="setQtte(this, 'qteProduct3');" class="referenceContentID" id="product3" />
        <img src="../ressources/quantite_plus.gif" class="qtte" width="14" height="10" border="0" alt="Ajouter" onclick="set_qte('Product3','+1')" />
        <img src="../ressources/quantite_moins.gif" class="qtte" width="14" height="10" border="0" alt="Retirer" onclick="set_qte('Product3','-1')" />
        <input type="text" id="qteProduct3" class="qtte" onblur="set_qte('Product3', this.value)" value="" /><br />
      <label for="product4">Produit 4 : </label><input type="text" value="" onBlur="setQtte(this, 'qteProduct4');" class="referenceContentID" id="product4" />
        <img src="../ressources/quantite_plus.gif" class="qtte" width="14" height="10" border="0" alt="Ajouter" onclick="set_qte('Product4','+1')" />
        <img src="../ressources/quantite_moins.gif" class="qtte" width="14" height="10" border="0" alt="Retirer" onclick="set_qte('Product4','-1')" />
        <input type="text" id="qteProduct4" class="qtte" onblur="set_qte('Product4', this.value)" value="" /><br />
      <label for="product5">Produit 5 : </label><input type="text" value="" onBlur="setQtte(this, 'qteProduct5');" class="referenceContentID" id="product5" />
        <img src="../ressources/quantite_plus.gif" class="qtte" width="14" height="10" border="0" alt="Ajouter" onclick="set_qte('Product5','+1')" />
        <img src="../ressources/quantite_moins.gif" class="qtte" width="14" height="10" border="0" alt="Retirer" onclick="set_qte('Product5','-1')" />
        <input type="text" id="qteProduct5" class="qtte" onblur="set_qte('Product5', this.value)" value="" /><br />
        <br />
      <button name="addProds" value="Valider" onclick="AddProducts();">Valider</button>
      <button name="cancelProds" value="Annuler" onclick="$('#ProductChooserWindow').hide();$('#ProductChooserWindowShad').hide();">Annuler</button>
    </div>
  </div>
</div> 
