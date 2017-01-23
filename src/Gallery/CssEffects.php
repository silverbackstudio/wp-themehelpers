<?php 

namespace Svbk\WP\Helpers\Gallery;

class CssEffects {
    
    public static function crossfade($selector, $count, $showtime=4, $transition=2 ){
    
    	static $instance = 0; $instance++;
    
    	$duration = ($showtime+$transition)*$count; ?>
    	<style type="text/css">
    		@keyframes crossfade<?php echo $instance ?> {
    			0%, <?php echo ceil($showtime/$duration*100); ?>%, 100%  { opacity:1; }
    			<?php echo ceil(1/$count*100); ?>%, <?php echo 100-ceil(($transition/$duration)*100); ?>% { opacity:0; }
    		}
    
    		<?php echo $selector; ?> {
    			animation: crossfade<?php echo $instance ?> <?php echo ($showtime*$count); ?>s ease-in-out infinite;
    		}
    
    		<?php for($i=1; $i<=$count; $i++): ?>
    		<?php echo $selector; ?>:nth-of-type(<?php echo $i ?>) { animation-delay: <?php echo $showtime*($count-$i) ?>s; }
    		<?php endfor; ?>
    	</style>
    	<?php
    }    
    
}