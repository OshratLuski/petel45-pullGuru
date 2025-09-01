define(['jquery', 'core/ajax'], function($, ajax) {
    // Function to save anonymous state via AJAX
    const saveAnonymousState = (cmid, state) => {
        return ajax.call([{
            methodname: 'quiz_diagnosticstats_set_anonymousstate',
            args: { cmid: cmid, state: state }
        }])[0];
    };

    // Function to set the anonymous state
    const setAnonymousState = (isAnonymous) => {
        createAnonymousStripe();
        setStatesFromConfig(isAnonymous);
        handleStudentNames(isAnonymous);
        const anonymousToggler = $('#anonymousmodeToggler1');
        anonymousToggler.prop('checked', isAnonymous);
    };

    // Function to create the anonymous stripe if it doesn't exist
    const createAnonymousStripe = () => {
        if (document.getElementById('anonymousStripe')) {
            return; // Already exists
        }

        const stripeContent = `
            <div class="d-flex align-items-center diagnosticstats_report-toggle anonymousmode">
                <input type="checkbox" id="anonymousmodeToggler3" class="toggle-btn d-none">
                <label for="anonymousmodeToggler2" class="mb-0 anonymousmodeToggler d-none"></label>
                <span class="link-btn-text ml-3"></span>
            </div>
            <i class="fas fa-user-secret ml-4"></i>
        `;

        const navbar = document.querySelector('nav.navbar');
        if (!navbar) {
            return; // Navbar not found
        }

        const stripeElement = document.createElement('div');
        stripeElement.classList.add('sticky-stripe', 'align-items-center', 'justify-content-center', 'yellow', 'hidden');
        stripeElement.id = 'anonymousStripe';
        stripeElement.innerHTML = stripeContent;
        navbar.insertAdjacentElement('afterbegin', stripeElement);
    };

    // Function to update the state of anonymous mode
    const setStatesFromConfig = (isAnonymous) => {
        createAnonymousStripe(); // Ensure the stripe exists

        const toggleTextElement = $('.diagnosticstats_report-toggle.anonymousmode .link-btn-text');
        if (!toggleTextElement.length) {
            return; // Element not found
        }

        const anonymousStripe = $('#anonymousStripe');
        const toggler = $('#anonymousmodeToggler1');

        if (isAnonymous) {
            anonymousStripe.removeClass('hidden');
            const stateText = toggler.closest('.diagnosticstats_report-toggle').data('texton');
            toggleTextElement.html(stateText || 'Anonymous mode ON');
            toggler.prop('checked', true);
        } else {
            anonymousStripe.addClass('hidden');
            const stateText = toggler.closest('.diagnosticstats_report-toggle').data('textoff');
            toggleTextElement.html(stateText || 'Anonymous mode OFF');
            toggler.prop('checked', false);
        }
    };

    let studentNamesProcessed = false;

    // Function to handle student name changes
    const handleStudentNames = (isAnonymous) => {
        if (!isAnonymous) {
            studentNamesProcessed = false;
        }

        if (studentNamesProcessed && isAnonymous) {
            console.warn('handleStudentNames already processed in anonymous mode.');
            return;
        }

        const studentNameMap = new Map();
        let anonymousCounter = 1;

        $('.student-name').each(function(index, el) {
            const $el = $(el);
            const userId = $el.attr('id')?.replace('student-', '');
            const studentLabel = M.util.get_string('studentlabel', 'quiz_diagnosticstats');

            if (!userId) {
                return; // No user ID found
            }

            if (!$el.data('originalName') && $el.text().trim() !== '' && $el.text().trim() !== 'Unknown') {
                $el.data('originalName', $el.text());
            }

            if (isAnonymous) {
                if (!studentNameMap.has(userId)) {
                    studentNameMap.set(userId, `${studentLabel} ${anonymousCounter}`);
                    anonymousCounter++;
                }
                $el.text(studentNameMap.get(userId));
            } else {
                const originalName = $el.data('originalName');
                if (!originalName) {
                    console.warn(`Original name not found for userId ${userId}. Defaulting to 'Unknown'.`);
                }
                $el.text(originalName || 'Unknown');
            }
        });

    };

    const updateAnonymousState = (isAnonymous) => {
        localStorage.setItem('anonymousState', isAnonymous ? '1' : '0');
    };

    //111addeventlistener
    window.addEventListener('storage', (event) => {
        if (event.key === 'anonymousState') {
            const isAnonymous = event.newValue === '1';
            const anonymousToggler = $('#anonymousmodeToggler1');
            setStatesFromConfig(isAnonymous);
            handleStudentNames(isAnonymous);
            anonymousToggler.prop('checked', isAnonymous);
        }
    });

    // Initialization function for the diagnostic questions table
    const initDiagnosticQuestionsTable = (cmid, anonymousStateFromConfig) => {

        // Check if a state is stored in localStorage; if not, fallback to the state from config
        const storedState = localStorage.getItem('anonymousState');
        const initialState = storedState !== null ? storedState === '1' : anonymousStateFromConfig;
        setAnonymousState(initialState);

        const anonymousToggler = $('#anonymousmodeToggler1');
        if (!anonymousToggler.length) {
            console.warn('Toggler not found');
            return; // Toggler not found
        }

        anonymousToggler.prop('checked', initialState);

        anonymousToggler.off('change');

        anonymousToggler.on('change', function() {
            const isAnonymous = this.checked;
            setStatesFromConfig(isAnonymous);
            handleStudentNames(isAnonymous);

            // Save the state to the server
            saveAnonymousState(cmid, isAnonymous ? 1 : 0).then((response) => {
                updateAnonymousState(isAnonymous);
            }).catch((error) => {
                console.error('Failed to save anonymous state:', error);
            });
        });
    };

    const init = (config) => {
        config = JSON.parse(config);
        // Convert cmid to an integer.
        config.cmid = parseInt(config.cmid, 10);

        setAnonymousState(config.anonymousState);
        initDiagnosticQuestionsTable(config.cmid, config.anonymousState);
    };

    return {
        init,
        initdiagnosticquestionstable: initDiagnosticQuestionsTable,
        setAnonymousState: setAnonymousState
    };
});
