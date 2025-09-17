/**
 * Admin Session Recording - Admin JavaScript
 * 
 * @package Admin_Session_Recording
 */

(() => {
	'use strict';

	// DOM elements
	const serviceSelect = document.getElementById('tracking-service-select');
	const serviceConfig = document.getElementById('service-config');
	const hotjarGroup = document.getElementById('hotjar-id-group');
	const clarityGroup = document.getElementById('clarity-id-group');
	const frontendPathsContainer = document.getElementById('frontend-paths-container');
	const addPathButton = document.getElementById('add-path');

	/**
	 * Toggle form groups based on selected service
	 */
	const toggleFields = () => {
		if (!serviceSelect) return;

		const selectedService = serviceSelect.value;

		// Hide all configuration when disabled
		if (selectedService === 'disabled') {
			hideGroup(serviceConfig);
			return;
		}

		// Show the main service configuration container
		showGroup(serviceConfig);

		// Hide service-specific groups first
		hideGroup(hotjarGroup);
		hideGroup(clarityGroup);

		// Show the relevant service-specific group
		if (selectedService === 'hotjar') {
			showGroup(hotjarGroup);
		} else if (selectedService === 'clarity') {
			showGroup(clarityGroup);
		}
	};

	/**
	 * Hide a form group
	 * 
	 * @param {HTMLElement} group - The form group element to hide
	 */
	const hideGroup = (group) => {
		if (group) {
			group.style.display = 'none';
		}
	};

	/**
	 * Show a form group
	 * 
	 * @param {HTMLElement} group - The form group element to show
	 */
	const showGroup = (group) => {
		if (group) {
			group.style.display = '';
		}
	};

	/**
	 * Add a new path entry
	 */
	const addPathEntry = () => {
		if (!frontendPathsContainer) return;

		const currentEntries = frontendPathsContainer.querySelectorAll('.path-entry');
		const newIndex = currentEntries.length;

		const pathEntryHTML = `
			<div class="path-entry" data-index="${newIndex}">
				<div class="path-input-group">
					<input type="text" 
						   name="admin_session_recording_options[frontend_paths][${newIndex}][path]" 
						   value="" 
						   placeholder="Enter path pattern (e.g., /products/, /about/)" 
						   class="form-input path-input" />
					<select name="admin_session_recording_options[frontend_paths][${newIndex}][match_type]" class="form-select match-type-select">
						<option value="contains">Contains</option>
						<option value="starts_with">Starts with</option>
						<option value="ends_with">Ends with</option>
						<option value="regex">Regex</option>
					</select>
					<button type="button" class="button remove-path">Remove</button>
				</div>
			</div>
		`;

		frontendPathsContainer.insertAdjacentHTML('beforeend', pathEntryHTML);
		updateRemoveButtons();
	};

	/**
	 * Remove a path entry
	 */
	const removePathEntry = (button) => {
		const pathEntry = button.closest('.path-entry');
		if (pathEntry && frontendPathsContainer.querySelectorAll('.path-entry').length > 1) {
			pathEntry.remove();
			updateRemoveButtons();
			updateFieldNames();
		}
	};

	/**
	 * Update remove button visibility
	 */
	const updateRemoveButtons = () => {
		const pathEntries = frontendPathsContainer.querySelectorAll('.path-entry');
		const removeButtons = frontendPathsContainer.querySelectorAll('.remove-path');
		
		removeButtons.forEach(button => {
			button.style.display = pathEntries.length > 1 ? '' : 'none';
		});
	};

	/**
	 * Update field names to maintain sequential indexing
	 */
	const updateFieldNames = () => {
		const pathEntries = frontendPathsContainer.querySelectorAll('.path-entry');
		
		pathEntries.forEach((entry, index) => {
			entry.setAttribute('data-index', index);
			
			const pathInput = entry.querySelector('.path-input');
			const matchTypeSelect = entry.querySelector('.match-type-select');
			
			if (pathInput) {
				pathInput.name = `admin_session_recording_options[frontend_paths][${index}][path]`;
			}
			if (matchTypeSelect) {
				matchTypeSelect.name = `admin_session_recording_options[frontend_paths][${index}][match_type]`;
			}
		});
	};

	/**
	 * Initialize the admin interface
	 */
	const init = () => {
		// Initialize on page load
		toggleFields();

		// Handle dropdown changes
		if (serviceSelect) {
			serviceSelect.addEventListener('change', toggleFields);
		}

		// Handle add path button
		if (addPathButton) {
			addPathButton.addEventListener('click', addPathEntry);
		}

		// Handle remove path buttons (delegated event listener)
		if (frontendPathsContainer) {
			frontendPathsContainer.addEventListener('click', (e) => {
				if (e.target.classList.contains('remove-path')) {
					removePathEntry(e.target);
				}
			});
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
