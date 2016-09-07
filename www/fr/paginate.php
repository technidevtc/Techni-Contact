<?php
function paginate($url, $link, $total, $current, $adj=3) {
	$prev = $current - 1; // numéro de la page précédente
	$next = $current + 1; // numéro de la page suivante
	$penultimate = $total - 1; // numéro de l'avant-dernière page
	$pagination = ''; // variable retour de la fonction : vide tant qu'il n'y a pas au moins 2 pages
	if ($total > 1) {
		$pagination .= "<div class=\"pagination\">\n";
		if ($current == 2) {
			$pagination .= "<a class='clickable' href=\"{$url}\">Précédent   </a>";
		} elseif ($current > 2) {
			$pagination .= "<a class='clickable' href=\"{$url}{$link}{$prev}\">Précédent   </a>";
		} else {
			$pagination .= '<span class="inactive">   </span>';
		}
		if ($total < 7 + ($adj * 2)) {
			$pagination .= ($current == 1) ? '<span class="active">1 | </span>' : "<a href=\"{$url}\"> 1 | </a>"; // Opérateur ternaire : (condition) ? 'valeur si vrai' : 'valeur si fausse'
			for ($i=2; $i<=$total; $i++) {
				if ($i == $current) {
					$pagination .= "<span class=\"active\"> {$i} | </span>";
				} else {
					$pagination .= "<a class='clickable' href=\"{$url}{$link}{$i}\"> {$i} | </a>";
				}
			}
		}
		else {
			if ($current < 2 + ($adj * 2)) {
				$pagination .= ($current == 1) ? "<span class=\"active\">1</span>" : "<a href=\"{$url}\">1</a>";
				for ($i = 2; $i < 4 + ($adj * 2); $i++) {
					if ($i == $current) {
						$pagination .= "<span class=\"active\"> {$i}  </span>";
					} else {
						$pagination .= "<a href=\"{$url}{$link}{$i}\"> {$i}  </a>";
					}
				}
				$pagination .= '&hellip;';
				$pagination .= "<a href=\"{$url}{$link}{$penultimate}\">{$penultimate}</a>";
				$pagination .= "<a href=\"{$url}{$link}{$total}\">{$total}</a>";
			}
			elseif ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) ) {
				$pagination .= "<a href=\"{$url}\">1</a>";
				$pagination .= "<a href=\"{$url}{$link}2\">2</a>";
				$pagination .= '&hellip;';
				for ($i = $current - $adj; $i <= $current + $adj; $i++) {
					if ($i == $current) {
						$pagination .= "<span class=\"active\">{$i}  </span>";
					} else {
						$pagination .= "<a href=\"{$url}{$link}{$i}\">{$i}  </a>";
					}
				}
				$pagination .= '&hellip;';
				$pagination .= "<a href=\"{$url}{$link}{$penultimate}\">{$penultimate}</a>";
				$pagination .= "<a href=\"{$url}{$link}{$total}\">{$total}</a>";
			}
			else {
				$pagination .= "<a href=\"{$url}\">1 | </a>";
				$pagination .= "<a href=\"{$url}{$link}2\">2 | </a>";
				$pagination .= '&hellip;';
				for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++) {
					if ($i == $current) {
						$pagination .= "<span class=\"active\"> {$i} | </span>";
					} else {
						$pagination .= "<a href=\"{$url}{$link}{$i}\"> {$i} | </a>";
					}
				}
			}
		}
		if ($current == $total)
			$pagination .= "<span class=\"inactive\">Suivant  </span>\n";
		else
			$pagination .= "<a href=\"{$url}{$link}{$next}\"> Suivant  </a>\n";
		$pagination .= "</div>\n";
	}

	return ($pagination);
}
?>