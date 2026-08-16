<?php

/**
* Class for AdminServ sort processing
*/
class AdminServSort {
	
	
	public static function sortByNickName(array $a, array $b): int{
		$a = TmNick::toText($a['NickName']);
		$b = TmNick::toText($b['NickName']);
        return $a <=> $b;
	}
	public static function sortByLadderRanking(array $a, array $b): int
    {
        return $a['LadderRanking'] <=> $b['LadderRanking'];
    }
	public static function sortByLogin(array $a, array $b): int
    {
        return $a['Login'] <=> $b['Login'];
    }
	public static function sortByStatus(array $a, array $b): int
    {
        return $a['SpectatorStatus'] <=> $b['SpectatorStatus'];
    }
	public static function sortByTeam(array $a, array $b): int{
		if($a['TeamId'] == 0){
			$a['TeamId'] = 'blue';
		}else if($a['TeamId'] == 1){
			$a['TeamId'] = 'red';
		}else{
			$a['TeamId'] = 'spectator';
		}
		if($b['TeamId'] == 0){
			$b['TeamId'] = 'blue';
		}else if($b['TeamId'] == 1){
			$b['TeamId'] = 'red';
		}else{
			$b['TeamId'] = 'spectator';
		}
        return $a['TeamId'] <=> $b['TeamId'];
	}
	
	/* Maps-list */
	public static function sortByFileName(array $a, array $b): int
    {
        return $a['FileName'] <=> $b['FileName'];
    }
	public static function sortByName(array $a, array $b): int{
		$a = strip_tags((string) $a['Name']);
		$b = strip_tags((string) $b['Name']);
        return $a <=> $b;
	}
	public static function sortByEnviro(array $a, array $b): int{
		if($a['Environment'] == 'Speed'){
			$a['Environment'] = 'Desert';
		}
		if($b['Environment'] == 'Speed'){
			$b['Environment'] = 'Desert';
		}
		if($a['Environment'] == 'Alpine'){
			$a['Environment'] = 'Snow';
		}
		if($b['Environment'] == 'Alpine'){
			$b['Environment'] = 'Snow';
		}
        return $a['Environment'] <=> $b['Environment'];
	}
	public static function sortByType(array $a, array $b): int
    {
        return $a['Type']['Name'] <=> $b['Type']['Name'];
    }
	public static function sortByAuthor(array $a, array $b): int{
		$a = strtolower((string) $a['Author']);
		$b = strtolower((string) $b['Author']);
        return $a <=> $b;
	}
	public static function sortByGoldTime(array $a, array $b): int
    {
        return $a['GoldTime'] <=> $b['GoldTime'];
    }
	public static function sortByPrice(array $a, array $b): int
    {
        return $a['CopperPrice'] <=> $b['CopperPrice'];
    }
	public static function sortByRank(array $a, array $b): int
    {
        return $a['Rank'] <=> $b['Rank'];
    }
}
?>