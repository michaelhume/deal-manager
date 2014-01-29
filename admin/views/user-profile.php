<h3>Deal Manager Client Information</h3>

	<table class="form-table">

		<tr>
			<th><label for="twitter">Twitter</label></th>
			
			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_user_meta( $user->ID,  'twitter', true ) ); ?>" class="regular-text" /><br />
				<span class="description">Please enter your Twitter username.</span>
			</td>
		</tr>

	</table>