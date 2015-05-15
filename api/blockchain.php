<?php
/**
 * Crypto Blockchain SQL to Json interface
 * Version      : 0.1.0
 * Last Updated : 5/13/2015
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

    static public function getInfo()
    {
        $wallet  = new jsonRPCClient(HOTWALLET, true);
        $getInfo = $wallet->getinfo();
        return $getInfo;
    }

    static public function getLatestBlocks($limit)
    {
        $findAll = R::findAll('blocks', 'order by id desc limit ?', [$limit]);
        $i       = 0;
        foreach ($findAll as $findOne) {
            $return[$i]['block']            = $findOne->height;
            $return[$i]['hash']             = $findOne->hash;
            $return[$i]['time']             = strtotime($findOne->time);
            $return[$i]['mint']             = $findOne->mint;
            $return[$i]['flags']            = $findOne->flags;
            $return[$i]['value']            = $findOne->totalvalue;
            $return[$i]['transactioncount'] = $findOne->transactioncount;
            $return[$i]['tx']               = $findOne->tx;
            $i++;
        }
        return $return;
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
        $i      = 0;
        $sql    = "
        (SELECT time,value,'send' as type,txid FROM `vin` WHERE `address` LIKE '" . $address . "')
        UNION
        (SELECT time,value,'receive' as type,txidp as txid FROM `vout` WHERE `address` LIKE '" . $address . "')
        order by time desc LIMIT " . $limit . "";
        $getAll = R::getAll($sql);
        foreach ($getAll as $findOne) {
            foreach ($findOne as $key => $value) {
                $return['data'][$i][$key] = $value;
            }
            $i++;
        }
        $return['count']   = count($getAll);
        $return['address'] = $address;
        $return['limit']   = $limit;
        return $return;
    }
}