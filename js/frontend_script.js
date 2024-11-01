/**
 * Script for the frontend.
 *
 * @package mailpoet_subscriber_overview
 */

jQuery(
	function ($) {
		$( 'select.sequel' ).select2();

		if ( $( '#subscriber-doughnut' ).length) {
			let ctx = document.getElementById( 'subscriber-doughnut' ).getContext( '2d' );

			let data_array   = [];
			let labels_array = [];
			let colors_array = [];

			$( "#donut-amounts div" ).each(
				function () {
					let amount = $( this ).attr( 'data-amount' );
					data_array.push( amount );
					let label = $( this ).attr( 'data-label' );
					labels_array.push( label );
					let color = $( this ).attr( 'data-color' );
					colors_array.push( color );
				}
			);

			console.log( colors_array );

			let data = {
				datasets: [{
					data: data_array,
					backgroundColor: colors_array
				}],

				// These labels appear in the legend and in the tooltips when hovering different arcs.
				labels: labels_array
			};
			new Chart(
				ctx,
				{
					type: 'doughnut',
					data: data,
					options: {
						legend: {
							onClick: ( e ) => e.stopPropagation()
						}
					}
				}
			);
		}

	}
);
