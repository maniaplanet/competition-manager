<?php
/**
 * @copyright   Copyright (c) 2009-2012 NADEO (http://www.nadeo.com)
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version     $Revision: 9021 $:
 * @author      $Author: gwendal $:
 * @date        $Date: 2012-11-30 18:12:04 +0100 (ven., 30 nov. 2012) $:
 */

namespace CompetitionManager\Services;

class ServerService extends \DedicatedManager\Services\ServerService
{
	const CRYPT_KEY = 'Dedicated account for CompetitionManager';
	
	/**
	 * @return Server[]
	 */
	function getLives()
	{
		$result = $this->db()->execute('SELECT * FROM Servers');
		return Server::arrayFromRecordSet($result);
	}

	/**
	 * @param string $rpcHost
	 * @param int $rpcPort
	 * @return Server
	 */
	function get($rpcHost, $rpcPort)
	{
		$result = $this->db()->execute(
				'SELECT * FROM Servers WHERE rpcHost=%s AND rpcPort=%d', $this->db()->quote($rpcHost), $rpcPort
			);
		return Server::fromRecordSet($result);
	}
	
	/**
	 * @param int $matchId
	 * @return Server
	 */
	function getByMatch($matchId)
	{
		$result = $this->db()->execute('SELECT * FROM Servers WHERE matchId=%d LIMIT 1', $matchId);
		try
		{
			$server = Server::fromRecordSet($result);
			$server->fetchDetails();
			return $server;
		}
		catch(\Exception $e)
		{
			return null;
		}
	}
	
	/**
	 * @param string $rpcHost
	 * @param int $rpcPort
	 * @param int $matchId
	 */
	function assignMatch($rpcHost, $rpcPort, $matchId)
	{
		$this->db()->execute(
				'UPDATE Servers SET startTime=NOW(), matchId=%d WHERE rpcHost=%s AND rpcPort=%d',
				$matchId,
				$this->db()->quote($rpcHost),
				$rpcPort
			);
	}

	/**
	 * @return DedicatedAccount[]
	 */
	function getAllAccounts()
	{
		$result = $this->db()->execute('SELECT * FROM DedicatedAccounts');
		return DedicatedAccount::arrayFromRecordSet($result);
	}
	
	/**
	 * @return DedicatedAccount
	 */
	function getAvailableAccount()
	{
		$result = $this->db()->execute('SELECT * FROM DedicatedAccounts WHERE rpcHost IS NULL LIMIT 1');
		return DedicatedAccount::fromRecordSet($result, false);
	}
	
	/**
	 * @param string $login
	 * @param string $rpcHost
	 * @param int $rpcPort
	 */
	function useAccount($login, $rpcHost, $rpcPort)
	{
		$this->db()->execute(
				'UPDATE DedicatedAccounts SET rpcHost=%s, rpcPort=%d WHERE login=%s',
				$this->db()->quote($rpcHost),
				$rpcPort,
				$this->db()->quote($login)
			);
	}

	/**
	 * @param string $login
	 * @param string $password
	 */
	function addAccount($login, $password)
	{
		$this->db()->execute(
				'INSERT INTO DedicatedAccounts(login, password) VALUES (%s, %s)',
				$this->db()->quote($login),
				$this->db()->quote(self::crypt($password))
			);
	}

	/**
	 * @param string[] $logins
	 */
	function removeAccounts($logins)
	{
		$this->db()->execute(
				'DELETE FROM DedicatedAccounts WHERE login IN (%s)',
				implode(',', array_map(array($this->db(), 'quote'), $logins))
			);
	}

	/**
	 * Crypt AES 128
	 * @param string $data
	 * @return string
	 */
	static function crypt($data)
	{
		// Set a random salt
		$salt = openssl_random_pseudo_bytes(8);
		$salted = '';
		$dx = '';
		// Salt the key(16) and iv(16) = 32
		while(strlen($salted) < 32)
		{
			$dx = md5($dx.self::CRYPT_KEY.$salt, true);
			$salted .= $dx;
		}

		$key = substr($salted, 0, 16);
		$iv = substr($salted, 16, 16);

		$encrypted_data = @openssl_encrypt($data, 'aes-128-cbc', $key, true);//, $iv);
		return base64_encode('Salted__'.$salt.$encrypted_data);
	}

	/**
	 * Decrypt AES 128
	 * @param string $edata
	 * @return string
	 */
	static function decrypt($edata)
	{
		$data = base64_decode($edata);
		$salt = substr($data, 8, 8);
		$ct = substr($data, 16);
		$data00 = self::CRYPT_KEY.$salt;
		$result = $md5_hash = md5($data00, true);
		for($i = 1; $i < 2; $i++)
		{
			$md5_hash = md5($md5_hash.$data00, true);
			$result .= $md5_hash;
		}
		$key = substr($result, 0, 16);
		$iv = substr($result, 16, 16);

		return @openssl_decrypt($ct, 'aes-128-cbc', $key, true);//, $iv);
	}
}

?>
