var klaroConfig = {
    mustConsent: false,
    testing: false,
    acceptAll: true,
    privacyPolicyUrl: 'https://www.ub.uni-frankfurt.de/benutzung/datenschutz.html',
    translations: {
        de: {
            privacyPolicyUrl: 'https://www.ub.uni-frankfurt.de/benutzung/datenschutz.html',
            consentNotice: {
                description: 'Diese Internetseite nutzt Cookies zu folgenden Zwecken: {purposes}.',
                learnMore: 'Datenschutzeinstellungen anzeigen'
            },
            consentModal: {
                description:
                    'Hier können Sie einsehen und anpassen, welche Informationen erhoben werden.'
            },
            purposes: {
                analytics: {
                    title: 'Benutzungsstatistiken'
                },
                security: {
                    title: 'Basisfunktionalität'
                }
            }
        },
        en: {
            privacyPolicyUrl: 'https://www.ub.uni-frankfurt.de/benutzung/datenschutz.html',
            consentNotice: {
                description: 'This website uses cookies for the following purposes: {purposes}.',
                learnMore: 'Show privacy settings'
            },
            consentModal: {
                description:
                    'Here you can see and customize the information that is collected. '
            },
            purposes: {
                analytics: {
                    title: 'Analytics'
                },
                security: {
                    title: 'Basic functionality'
                }
            }
        }
    },
    services: [
        {
            name: 'needed',
            default: true,
            required: true,
            purposes: ['security'],
            translations: {
                de: {
                    title: "Notwendige Cookies"
                },
                en: {
                    title: "Required cookies"
                }
            }
        },
        {
            name: 'matomo',
            default: true,
            required: false,
            onlyOnce: true,
            purposes: ['analytics'],
            translations: {
                zz: {
                    title: 'Matomo'
                },
                en: {
                    description: 'Matomo is a service for usage statistics hosted at the UB Frankfurt a. M.'
                },
                de: {
                    description: 'Matomo ist eine von der UB Frankfurt a. M. betriebene Software für Benutzungsstatistiken.'
                }
            },
            cookies: [
                [/^_pk_.*$/, '/', 'performing-arts.eu'],
                [/^_pk_.*$/, '/', 'localhost'],
                'piwik_ignore'
            ]
        }
    ]
};
