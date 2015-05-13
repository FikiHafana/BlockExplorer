<?php
/**
 * Created by PhpStorm.
 * User: YoshiGaming
 * Date: 5/13/2015
 * Time: 1:10 PM
 */

/**
 * Class blockchain
 */
class blockchain
{
    /**
     * @return blockchain
     */
    static public function api()
    {
        return new self;
    }

    static public function blockLookup($block)
    {
        $findOne = R::findOne('blocks', 'height = ?', [$block]);
        foreach ($findOne as $key => $value) {
            if ($key == "tx") {
                $return[$key] = json_decode($value, true);
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    static public function transactionLookup($tx)
    {
        $wallet                   = new jsonRPCClient(HOTWALLET, true);
        $getRawTransaction        = $wallet->getrawtransaction($tx);
        $decodeRawTransaction     = $wallet->decoderawtransaction($getRawTransaction);
        $return['rawtransaction'] = $getRawTransaction;
        $return['transaction']    = $decodeRawTransaction;
        return $return;
    }

    static public function addressLookup($address, $limit = 10)
    {
        $i       = 0;
        $findAll = R::findAll('vout', 'addresses LIKE ? order by id desc LIMIT ?, 0', ['%' . $address . '%',$limit]);
        foreach ($findAll as $findOne) {
            foreach ($findOne as $key => $value) {
                $return['data'][$i][$key] = $value;
            }
            $i++;
        }
        $return['count']   = count($findAll);
        $return['address'] = $address;
        $return['limit']   = $limit;
        return $return;
    }
}