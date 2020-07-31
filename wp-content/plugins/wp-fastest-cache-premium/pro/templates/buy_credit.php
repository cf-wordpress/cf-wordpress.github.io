<div id="wpfc-buy-credit-content" class="mainContent">
    <form action="http://api.wpfastestcache.net/paypal/buyimagecredit/" method="post">
        <input type="hidden" name="hostname" value="<?php echo str_replace(array("http://", "www."), "", $_SERVER["HTTP_HOST"]); ?>">
        <div class="pageView"style="display: block;">
            <div class="fakeHeader">
                <h3 class="title-h3">Choose the amount of Image Credit you'd like to add.</h3>
            </div>
            <div class="fieldRow radioList active">
                <ul role="menu" id="wpfc-product-selection-list" class="">
                    <?php
                        if(function_exists('curl_exec')){
                            $ch = curl_init();
                         
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_URL, "http://api.wpfastestcache.net/prices/imagecredit");
                         
                            $data = curl_exec($ch);
                            curl_close($ch);
                        }else{
                            $data = @file_get_contents("http://api.wpfastestcache.net/prices/imagecredit");
                        }

                        if($data){
                            $arr = json_decode($data);
                            foreach ($arr as $credit => $price) {
                                ?>
                                <li style="margin-bottom: 0;">
                                    <label class="">
                                        <span class="icon"></span>
                                        <input type="radio" name="quantity" value="<?php echo $credit; ?>">
                                        <span class="text">$<?php echo $price; ?>/<?php echo $credit;?> Credits</span>
                                    </label>
                                </li>
                                <?php
                            }
                        }else{
                            echo "Error Occured";
                        }
                    ?>
                </ul>
            </div>
            <div class="pagination">
                <div class="next">
                    <button class="wpfc-btn primaryCta" id="productSelection">
                        <span class="label">Continue</span>
                    </button>
                </div>
                <div class="prev">
                    <button class="wpfc-btn primaryNegativeCta" id="wpfc-cancel-buy-credit">
                        <span>Cancel</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>