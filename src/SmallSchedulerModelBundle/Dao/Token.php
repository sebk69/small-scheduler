<?php
namespace App\SmallSchedulerModelBundle\Dao;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

class Token extends AbstractDao
{
    protected function build()
    {
        $this->setDbTableName("token")
            ->setModelName("Token")
            ->addPrimaryKey("id", "id")
            ->addField("token", "token")
            ->addField("data", "data")
        ;
    }

    /**
     * Genrate a token
     * @param $data
     * @param int $size
     * @return \App\SmallSchedulerModelBundle\Model\Token
     */
    public function generate($data, $size = 64)
    {
        // Allowed chars
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        // Generate token randomly
        do {
            $token = "";
            $i = $size;
            while ($i > 0) {
                $token .= substr($chars, rand(0, strlen($chars) - 1), 1);
                $i--;
            }

            $exists = true;
            try {
                $this->findOneBy(["token" => $token]);
            } catch (\Exception $e) {
                $exists = false;
            }
        } while ($exists);

        // Persist token
        /** @var \App\SmallSchedulerModelBundle\Model\Token $tokenModel */
        $tokenModel = $this->newModel();
        $tokenModel->setToken($token);
        $tokenModel->setData(json_encode($data));
        $tokenModel->persist();

        return $tokenModel;
    }

    /**
     * Remove a token
     * @param $token
     * @throws \Sebk\SmallOrmBundle\Dao\DaoEmptyException
     * @throws \Sebk\SmallOrmBundle\Dao\DaoException
     */
    public function remove($token)
    {
        $this->findOneBy(["token" => $token])->delete();
    }
}