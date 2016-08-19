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

7. gzip web
    add .htaccess : 
    <IfModule mod_deflate.c>
        SetOutputFilter DEFLATE
        AddOutputFilterByType DEFLATE text/html text/css text/plain text/xml application/x-javascript application/x-httpd-php
        BrowserMatch ^Mozilla/4 gzip-only-text/html
        BrowserMatch ^Mozilla/4\.0[678] no-gzip
        BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
        BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
        SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png)$ no-gzip
        Header append Vary User-Agent env=!dont-vary
    </IfModule>

    # Expires Headers - 2678400s = 31 days
    <ifmodule mod_expires.c>
        ExpiresActive On
        ExpiresDefault "access plus 1 seconds"
        ExpiresByType text/html "access plus 7200 seconds"
        ExpiresByType image/gif "access plus 2678400 seconds"
        ExpiresByType image/jpeg "access plus 2678400 seconds"
        ExpiresByType image/png "access plus 2678400 seconds"
        ExpiresByType text/css "access plus 518400 seconds"
        ExpiresByType text/javascript "access plus 2678400 seconds"
        ExpiresByType application/x-javascript "access plus 2678400 seconds"
    </ifmodule>

    # Cache Headers
    <ifmodule mod_headers.c>
        # Cache specified files for 31 days
        <filesmatch "\.(ico|flv|jpg|jpeg|png|gif|css|swf)$">
            Header set Cache-Control "max-age=2678400, public"
        </filesmatch>
        # Cache HTML files for a couple hours
        <filesmatch "\.(html|htm)$">
            Header set Cache-Control "max-age=7200, private, must-revalidate"
        </filesmatch>
        # Cache PDFs for a day
        <filesmatch "\.(pdf)$">
            Header set Cache-Control "max-age=86400, public"
        </filesmatch>
        # Cache Javascripts for 31 days
        <filesmatch "\.(js)$">
            Header set Cache-Control "max-age=2678400, private"
        </filesmatch>
    </ifmodule>
7. textarea break line: nl2br(string)
8. wordpress delete post

    delete from `wp_woocommerce_order_itemmeta` where `order_item_id` IN (select `order_item_id` from `wp_woocommerce_order_items` join `wp_posts` on `wp_posts`.`ID` = `wp_woocommerce_order_items`.`order_id` where `post_status` = 'trash' and `post_type` = 'shop_order');
    
    delete from `wp_woocommerce_order_items` where `order_id` IN (select ID from `wp_posts` where `post_status` = 'trash' and `post_type` = 'shop_order');    


    delete from `wp_postmeta` where `post_id` IN (select ID from `wp_posts` where `post_status` = 'trash' and `post_type` = 'shop_order');

    delete from `wp_commentmeta` where `comment_id` IN (select `comment_ID` from `wp_comments` join `wp_posts` on `wp_posts`.`ID` = `wp_comments`.`comment_post_ID` where `post_status` = 'trash' and `post_type` = 'shop_order');
    
    delete from `wp_comments` where `comment_post_ID` IN (select ID from `wp_posts` where `post_status` = 'trash' and `post_type` = 'shop_order');

    delete from `wp_posts` where `post_status` = 'trash' and `post_type` = 'shop_order';
    