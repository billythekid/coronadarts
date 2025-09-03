import LoseLives from "./components/lose-lives.vue";
import CompetitionsDropdown from "./components/competitions-dropdown.vue";
import CumulativeScores from "./components/cumulative-scores.vue";
import PlayerDropdown from "./components/player-dropdown.vue";

require('./bootstrap');

import { createApp } from 'vue';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, initializing Vue...');

    // Check if we have a cumulative-scores element to mount
    const cumulativeScoresElement = document.querySelector('cumulative-scores');
    console.log('Looking for cumulative-scores element...', cumulativeScoresElement);

    // Check if we have a lose-lives element to mount
    const loseLivesElement = document.querySelector('lose-lives');
    console.log('Looking for lose-lives element...', loseLivesElement);

    if (cumulativeScoresElement) {
        console.log('Found cumulative-scores element, creating dedicated app...');

        // Get the props from the element attributes
        const startPlayers = cumulativeScoresElement.getAttribute('start-players');
        const game = cumulativeScoresElement.getAttribute('game');

        let parsedPlayers = [];
        try {
            if (startPlayers) {
                parsedPlayers = JSON.parse(startPlayers);
            }
        } catch (error) {
            console.error('Error parsing players JSON:', error);
        }

        // Create a dedicated app just for this component
        const cumulativeApp = createApp(CumulativeScores, {
            startPlayers: parsedPlayers,
            game: game
        });

        // Add global error handler
        cumulativeApp.config.errorHandler = (err, instance, info) => {
            console.error('Vue error:', err);
            console.error('Instance:', instance);
            console.error('Info:', info);
        };

        // Mount the app to replace the custom element
        try {
            const vueInstance = cumulativeApp.mount(cumulativeScoresElement);
            console.log('Cumulative scores app mounted successfully:', vueInstance);
            window.cumulativeApp = vueInstance;
        } catch (error) {
            console.error('Failed to mount cumulative scores app:', error);
        }
    }

    if (loseLivesElement) {
        console.log('Found lose-lives element, creating dedicated app...');

        // Get the props from the element attributes
        const startPlayers = loseLivesElement.getAttribute('start-players');
        const game = loseLivesElement.getAttribute('game');

        let parsedPlayers = [];
        try {
            if (startPlayers) {
                parsedPlayers = JSON.parse(startPlayers);
            }
        } catch (error) {
            console.error('Error parsing players JSON:', error);
        }

        // Create a dedicated app just for this component
        const loseLivesApp = createApp(LoseLives, {
            startPlayers: parsedPlayers || [],
            game: game
        });

        // Add global error handler
        loseLivesApp.config.errorHandler = (err, instance, info) => {
            console.error('Vue error in lose-lives:', err);
        };

        // Mount the app to replace the custom element
        try {
            const vueInstance = loseLivesApp.mount(loseLivesElement);
            console.log('Lose-lives app mounted successfully:', vueInstance);
            window.loseLivesApp = vueInstance;
        } catch (error) {
            console.error('Failed to mount lose-lives app:', error);
        }
    }

    // Create the main Vue app for other components only if no dedicated components found
    const appElement = document.getElementById('app');
    if (appElement && !cumulativeScoresElement && !loseLivesElement) {
        const app = createApp({
            delimiters: ['${', '}'],
            data() {
                return {
                    currentCompetition: null,
                    players: [],
                    competitions: [],
                    halfitData: []
                }
            },
            mounted() {
                this.competitions = typeof competitions !== 'undefined' ? competitions : [];
                console.log('Main Vue app mounted');
            },
            methods: {
                competitionChange(competition) {
                    this.currentCompetition = null;
                    if (competition) {
                        this.currentCompetition = competition;
                    }
                },
                halfitMounted(data) {
                    this.halfitData = data;
                    console.log('Halfit data received:', data);
                }
            }
        });

        // Register components
        app.component('lose-lives', LoseLives);
        app.component('competitions-dropdown', CompetitionsDropdown);
        app.component('player-dropdown', PlayerDropdown);

        try {
            window.app = app.mount('#app');
            console.log('Main Vue app mounted successfully');
        } catch (error) {
            console.error('Failed to mount main Vue app:', error);
        }
    }
});
