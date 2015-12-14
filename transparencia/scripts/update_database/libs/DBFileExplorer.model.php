<?php

/**
 * 
 * @author dbseller
 * @abstract
 */
abstract class DBFileExplorer {
	
	/**
	 * Mostra os Itens de Um diret�rio
	 * 
	 * @param string $sDiretorio
	 *        	-> Diret�rio a ser pesquisado
	 * @param boolean $lMostraDiretorios
	 *        	-> mostra pastas
	 * @param boolean $lMostraArquivos
	 *        	-> mostra arquivos
	 * @param string $sRegexpIgnorar
	 *        	-> express�o regular para ignorar casos Ex. $sRegexpIgnorar = "/CVS/";
	 * @param boolean $lRecursivo
	 *        	-> pesquisar diret�rio recursivamente
	 * @return $aRetorno - Array contendo a string dos itens encontrados nos diret�rios
	 */
	public static function listarDiretorio($sDiretorio, $lMostraDiretorios = true, $lMostraArquivos = true, $sRegexpIgnorar = null, $lRecursivo = false) {
		$aRetorno = array ();
		if (! is_dir ( $sDiretorio )) {
			throw new Exception ( "Nao e um diretorio." );
		}
		
		if (! is_readable ( $sDiretorio )) {
			throw new Exception ( "Diretorio n�o Pode ser Lido." );
		}
		
		$rDiretorio = opendir ( $sDiretorio );
		
		if (! $rDiretorio) {
			throw new Exception ( 'Nao foi possivel abrir o Diretorio' );
		}
		
		while ( ($sArquivo = readdir ( $rDiretorio )) !== false ) {
			
			$lAchouExpressao = is_null ( $sRegexpIgnorar ) ? false : preg_match ( $sRegexpIgnorar, $sArquivo );
			
			if ($sArquivo == "." || $sArquivo == ".." || $lAchouExpressao || $sArquivo == 'CVS') {
				continue;
			}
			
			if (is_dir ( "$sDiretorio/$sArquivo" ) && is_readable ( "$sDiretorio/$sArquivo" ) && $lRecursivo) {
				$aRetorno = array_merge ( $aRetorno, DBFileExplorer::listarDiretorio ( "$sDiretorio/$sArquivo", $lMostraDiretorios, $lMostraArquivos, $sRegexpIgnorar, $lRecursivo ) );
			}
			
			$lDiretorio = is_dir ( "$sDiretorio/$sArquivo" );
			$lMostraDiretorio = is_dir ( "$sDiretorio/$sArquivo" ) && $lMostraDiretorios;
			$lMostraArquivo = ! is_dir ( "$sDiretorio/$sArquivo" ) && $lMostraArquivos;
			
			if ($lMostraArquivo || $lMostraDiretorio) {
				$aRetorno [] = "{$sDiretorio}/{$sArquivo}";
			}
		}
		return $aRetorno;
	}
	
	/**
	 * Retorna o Caminho onde o arquivo foi encontrado
	 * 
	 * @param string $sDiretorio
	 *        	-> Diret�rio Raiz a ser pesquisado
	 * @param string $sNomeArquivo
	 *        	-> Nome do Arquivo a ser pesquisado
	 * @return $sRetorno - String com o caminho completo do arquivo solicitado
	 */
	public static function getCaminhoArquivo($sDiretorioRaiz, $sNomeArquivo) {
		if (! is_dir ( $sDiretorioRaiz )) {
			throw new Exception ( "N�o � um diret�rio." );
		}
		
		if (! $sNomeArquivo) {
			throw new Exception ( 'N�o foi Informado o nome do arquivo!' );
		}
		
		$sDirectoryScripts = $sDiretorioRaiz;
		
		if (is_file ( "{$sDiretorioRaiz}/{$sNomeArquivo}" ) && filesize ( "{$sDiretorioRaiz}/{$sNomeArquivo}" ) > 0)
			return "{$sDiretorioRaiz}/{$sNomeArquivo}";
		
		$aDiretorios = DBFileExplorer::listarDiretorio ( $sDiretorioRaiz, true, true, null, true );
		
		sort ( $aDiretorios );
		
		foreach ( $aDiretorios as $sDiretorio ) {
			
			$lExisteArquivo = is_file ( "{$sDiretorio}/{$sNomeArquivo}" );
			
			$aArquivosExecucao = array ();
			
			if ($lExisteArquivo && filesize ( "{$sDiretorio}" ) > 0)
				return "{$sDiretorio}/{$sNomeArquivo}";
		}
		
		return null;
	}
}