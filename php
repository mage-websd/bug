array_intersect(array1,array2): return array co value chung cua 2 array, key la key cua array1
array_merge(array1,array2): hop 2 mang
array_diff(array1,array2): tra ve mang gom cac phan tu khac nhau 2 mang

3. download
header('Content-Type: application/x-tar'); //text/txt, text/csv, application/pdf
header('Content-Length: ' . filesize($filePath));
header("Content-Transfer-Encoding: binary");
header("Content-disposition: attachment; filename=".$fileName);
readfile($filePath);

4. Tim kiem
class ApproximatString
{
    public $s;
    public $i, $j, $k, $loi, $saiSo;
    public function ApproximatString($nhap)
    {
        $s = $nhap;
        $saiSo = (int)Math.Round(strlen($s) * 0.3);
	}

	public bool SoSanh(string s1)
	{
    	if (strlen($s1) < (strlen($s) - saiSo) || strlen(s1) > (strlen($s) + saiSo)) {
        	return false;
    	}
    	$this->i = $this->j = $this->loi = 0;
    	while ($this->i < strlen($s) && $this->j < strlen($s1)) {
        	if ($s[$this->i] != $s1[$this->j]) {
    			$this->loi++;
	    		for ($k = 1; $k <= $saiSo; $k++) {
	    			if (($this->i + $k < strlen($s)) && $s[$this->i + $k] == $s1[$this->j]) {
	    				$this->i += $k;
	    				break;
					}
					else if (($this->j + $k < strlen($s1)) && $s[$this->i] == $s1[$this->j + $k]) {
	    				$this->j += $k;
	    				break;
					}                 
				}
        	}
    		             $this->i++;             $this->j++;         
        }
    $this->loi += strlen($s) - $this->i + strlen($s1) - $this->j;
            if ($this->loi <= $saiSo)
                         return true;
else return false;    }}

5. backgroud process 
function isRunning($pid=null){
    if(!$pid)
        return false;
    try{
        $result = shell_exec(sprintf("ps %d", $pid));
        if( count(preg_split("/\n/", $result)) > 2){
            return true;
        }
    }catch(Exception $e){}
    return false;
}
if(isset($_SESSION['pid']) && $_SESSION['pid'])
    $pid = $_SESSION['pid'];
else
    $pid = null;
if(!isRunning($pid)) {
    unset($_SESSION['pid']);
    echo 'running start';
    $pid = shell_exec(sprintf('%s > /dev/null 2>&1 & echo $!', 'php -f backgroud.php'));
    $_SESSION['pid'] = $pid;
}
else
    echo "Running, waiting";

6. javascript
    header("X-XSS-Protection: 0"); them vao dau file neu javascript boi php ko chay duoc