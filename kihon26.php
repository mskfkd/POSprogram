//90 Q.商品マスター登録プログラムを作成したい
//下記仕様をみたすプログラムを作成してください。

<?php


class Product{

	public static $product_list = array (
		1 => array (
			"name" => "おいしいかもしれない牛乳",
			"jancode" => "816208808001"
		),
		2 => array (
			"name" => "じゃがいも",
			"jancode" => "237784671002"
		)
	);

	public static function getProductList() {
		foreach ( self::$product_list as $key => $product ) {
			echo $key . $product["name"]. "\n";
		}
	}

	public static function addProduct( $input1,$input2 ) {

		$inputProd = array (
			"name" => $input1,
			"jancode" => $input2,
		);

		$result = array_push( self::$product_list, $inputProd );
		echo $result;
	}

	public static function deleteProduct( $input ) {
		unset( self::$product_list[$input] );
	}

}

class Operation{

	const PRODUCTLIST = "1";
	const PRODUCTINPUT = "2";
	const PRODUCTDELETE = "3";
	const PRODUCTLISTEXPORT = "4";
	const PRODUCTLISTIMPORT= "5";
	const OPERATIONFINISH = "6";

	const MENU_LIST = array(
		self::PRODUCTLIST => "1.商品一覧",
		self::PRODUCTINPUT => "2.商品登録",
		self::PRODUCTDELETE => "3.商品削除",
		self::PRODUCTLISTEXPORT => "4.商品一覧出力",
		self::PRODUCTLISTIMPORT=> "5.商品一覧入力",
		self::OPERATIONFINISH => "6.操作終了"
	);


	public function __construct() {
		echo "操作を1~6から選択してください。" . "\n";
		foreach( self::MENU_LIST as $value ){
			echo $value . "\n";
		}
	}
	
	public function createJanCode() {
		$this->jancode = random_int( 100000, 999999 );
		return $this->jancode;
	}

	public function showMenu() {
		echo "操作を1~6から選択してください。" . "\n";
		foreach( self::MENU_LIST as $value ){
			echo $value . "\n";
		}
	}

	public function selectMenu() {

		$input = rtrim(fgets(STDIN));

		if ( empty($input) ) {
			echo "認識できませんでした。再度入力してください。" . "\n";
			return $this->selectMenu();
		}

		if (!is_numeric($input) || !isset(self::MENU_LIST[$input])) {
			echo "1~6のいずれかで選択し直してください" . "\n";
			return $this->selectMenu();
		}

		switch ( $input ) {
			case self::PRODUCTLIST:
			$this->productlist();
			break;

			case self::PRODUCTINPUT:
			$this->registration();
			break;

			case self::PRODUCTDELETE:
			$this->delete();
			break;

			case self::PRODUCTLISTEXPORT:
			$this->export();
			break;

			case self::PRODUCTLISTIMPORT:
			$this->import();
			break;

			case self::OPERATIONFINISH:
			$this->finish();
			break;
		}

		if( $input < self::OPERATIONFINISH ) {

			echo "他にも操作されますか?" . "\n";
			$this->showMenu();
			$this->selectMenu();

		}
	}


	public function productlist() {
		echo "現在の登録されている商品一覧です。" . "\n";
		$res = Product::getProductList();
		return $res;
	}

	public function registration() {
		echo "登録したい商品名を入力してください。" . "\n";
		$inputName = rtrim(fgets(STDIN));

		if (empty($inputName)) {
			echo "認識できませんでした。もう一度入力してください。" . "\n";
			return $this->registration();
		}
		echo "以下の内容で登録しました。" . "\n";
		$inputJancode= $this->createJanCode();

		Product::addProduct( $inputName,$inputJancode );

		$res = Product::getProductList();
		return $res;
	}	

	public function delete() {
		echo "削除したい商品の番号を選択してください。" . "\n";

		$res = Product::getProductList();

		$input = rtrim( fgets(STDIN) );

		if( $input > count( Product::$product_list )
			|| $input == 0 ){
			echo "登録されている商品にありません" . "\n";
		$this->delete();
	}

	Product::deleteProduct( $input );

	echo "削除が完了しました。" . "\n";
	Product::getProductList();
	return;

	}

	public function export() {
		echo "商品一覧を出力します。" . "\n";

		$n_filepath = './csv/item_list_' . date( 'YmdHi' ) . '.csv';
		$n_filename = basename( $n_filepath );
		$id = 1;

		$titles = [ 'name' , 'jancode' ];
		$f_line = sprintf( '%s,%s' , $titles[0] , $titles[1] . "\n" );
		$op = fopen( $n_filepath, 'w' );
		fwrite( $op, $f_line );
		fclose( $op );

		$handle = fopen( $n_filepath, 'a' );
		if( !$n_filepath ) {
			echo "ファイルが見つかりません。" . "\n";
			return;
		}

		$products = Product::$product_list;

		foreach ( $products as $product ) {

			$name = $product['name'];
			$jancode = $product['jancode'];

			$line = sprintf( "%s,%s,%s", $id, $name, $jancode );

			$fp = fopen( $n_filepath, 'a' );

			fwrite( $fp, $line . "\n" );	
			$id++;
			fclose( $fp );
		}

		fclose( $handle );

		echo "出力が完了しました。" . "\n";

	}

	public function import() {

		$isFilePath = "./import/";
		$isFile = glob( "./import/*.csv" );

		if( !$isFile ) {
			echo "ファイルが見つかりません。" . "\n";
			return;
		}

		$i = 1;
		echo "どのファイルを取り込むか選択してください。" . "\n";
		$selectImportFile=[];
		foreach ( $isFile as $value ) {
			$selectImportFile[$i] = $value;
			echo $i . $value . "\n";
			$i++;
		}


		$fileno = rtrim( fgets(STDIN) );

		$content = file_get_contents( $selectImportFile[$fileno] );
		$prepare = explode( "\n", $content );

		if($content !== false){
			$changeFile = basename( $selectImportFile[$fileno] );
			rename( $selectImportFile[$fileno] , "./completed/". $changeFile);
		}

		foreach ( $prepare as $key => $value ) {
			$csv[ $key ] = $value; 
			$inputJancode= $this->createJanCode();
			Product::addProduct( $value,$inputJancode );

		}

		echo "一覧での登録が完了しました" . "\n";
		$res = Product::getProductList();
		echo $res . "\n";
		
	}	

	public function finish() {
		echo "操作を終了します。" . "\n";
		return;
	}

}


$pos = new Operation();
$pos->SelectMenu();

?>
