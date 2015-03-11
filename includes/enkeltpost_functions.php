<?php

function bestandsinfo ($status , $restriction) {
	switch ($status) {
		case "1":
			return "Ukjent status";
			break;
		case "2":
			return "I bestilling";
			break;
		case "3":
			return "Ukjent status, utilgjengelig";
			break;
		case "4":
			return "<strong>Utlånt</strong>";
			break;
		case "5":
			return "<strong>Utlånt</strong>";
			break;
		case "6":
			return "Under behandling";
			break;
		case "7":
			return "Innkalt";
			break;
		case "8":
			return "På vent";
			break;
		case "9":
			return "Venter på klargjøring";
			break;
		case "10":
			return "På vei mellom to bibliotek";
			break;
		case "11":
			return "Hevdet innlevert eller aldri lånt";
			break;
		case "12":
			return "<strong>Tapt</strong>";
			break;
		case "13":
			return "Savnet - vi leter";
			break;
		case "14":
			return "Ukjent status";
			break;
		case "15":
			return "Til innbinding";
			break;
		case "16":
			return "Til reparasjon";
			break;
		case "17":
			return "Venter på overføring";
			break;
		case "18":
			return "Purring sendt";
			break;
		case "19":
			return "Trukket tilbake";
			break;
		case "20":
			return "Ukjent status";
			break;
		case "21":
			return "Ukjent status";
			break;
		case "22":
			return "Skadet";
			break;
		case "23":
			return "Ikke i omløp";
			break;
		case "24":
			return "Annen status";
			break;
		case "0":
			switch ($restriction) {
				case "1":
					return "[Ikke til utlån]";
					break;
				case "2":
					return "<strong>Ledig</strong> [Til bruk i biblioteket]";
					break;
				case "3":
					return "<strong>Ledig</strong> [Dagslån]";
				case "4":
					return "<strong>Ledig</strong> [Til bruk på lesesal el.l.]";
					break;
				case "5":
					return "<strong>Ledig</strong> [Kan ikke fornyes]";
					break;				
				case "6":
					return "<strong>Ledig</strong> [Begrenset lånetid]";
					break;				
				case "8":
					return "<strong>Ledig</strong> [Utvidet lånetid]";
					break;
				case "9":
				case "10":
					return "<strong>Ledig</strong>";
					break;
				default:
					return "<strong>Ledig</strong>";
			}
		default:
			return "Ukjent status";
			break;
	}
}

?>
