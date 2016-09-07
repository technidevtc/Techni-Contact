<script language="JavaScript">
<!-- 

function traitement(numero)
{
<?php

for($i = 1; $i <= 10; ++$i)
{
     print('if(numero==\'' . $i . '\'){' . "\n");
     print('    parent.document.all.id' . $i . '.value  = document.all.id.value;' . "\n");
     print('    parent.document.all.nom' . $i . '.value = document.all.nom.value;' . "\n");
     print('    parent.document.all.ref' . $i . '.value = document.all.ref.value;' . "\n");
     print('    parent.document.all.fam' . $i . '.value = document.all.f.value;' . "\n");
     print('}' . "\n");

}

?>
}
// -->
</script>

<body bgcolor="#E9EFF8" topmargin="0" marginheight="0">
<div align="center"> 
  <input name="id" type="text" id="id" value="<?php print($_GET['id']) ?>" size="10" readonly="readonly">
  <input name="nom" type="text" id="nom" value="<?php print(to_entities(urldecode($_GET['name']))) ?>" readonly="readonly">
  <input type="hidden" name="f" value="<?php print($_GET['f']) ?>">
  <input type="hidden" name="ref" value="<?php print($_GET['ref']) ?>">
  <select name="select" onClick="javascript:traitement(this.value)">
    <option selected>Attribuer en position...</option>
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
    <option value="9">9</option>
    <option value="10">10</option>
  </select>
</div>

