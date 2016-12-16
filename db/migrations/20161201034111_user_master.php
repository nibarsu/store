<?php

use Phinx\Migration\AbstractMigration;

class UserMaster extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
      //帳號主檔
      $table = $this->table('USER_M',array('id'=>false ,'primary_key'=>'id'));
      $table
         ->addColumn('id','integer')
         ->addColumn('NAME','string' ,array('limit' => 20) )
         ->addColumn('PASS','string' ,array('limit' => 20) )
         ->addColumn('DID','integer' ,array('limit' => 3) )
         ->addColumn('SID','integer' ,array('limit' => 3) )
         //是否可轉倉 
         ->addColumn('STORE_FLAG','enum',array('values' => '0,1' ,'default' => '0' ,'comment'=>'can transfer goods to another store')  )
         ->save();
    }
}
