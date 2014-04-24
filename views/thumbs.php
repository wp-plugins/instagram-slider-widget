
<div class="instag">
    <ul class="thumbnails no-bullet">
		<?php
			if ( isset( $data_arr ) && is_array( $data_arr ) ) {
				foreach ( $data_arr as $data ) {
					foreach ( $data as $k => $v ) {
						$$k = $v;
					}
					
					if ( $link_to && 'user_url' == $link_to ) {
						$link = $user_url;
					}
					
					echo '<li>'. "\n";
					echo '<a target="_blank" href="'.$link.'"><img src="'.$image.'" alt="'.$text.'"></a>' . "\n";
					echo '</li>' . "\n";
				}
			}
        ?>
    </ul>
</div>