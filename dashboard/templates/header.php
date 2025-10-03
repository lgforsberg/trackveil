<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>Trackveil Dashboard</title>
    
    <!-- Tailwind CSS with inline config -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <style type="text/tailwindcss">
        @layer utilities {
            .btn-gradient {
                background-image: linear-gradient(135deg, #2DD4BF 0%, #38BDF8 100%);
            }
        }
    </style>
    <script>
        // Wait for Tailwind to load before configuring
        if (typeof tailwind !== 'undefined') {
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            navy: '#0F172A',
                            slate2: '#1E293B',
                            offwhite: '#F8FAFC',
                            teal: '#14B8A6',
                            sky: '#38BDF8',
                            turquoise: '#2DD4BF',
                            purple: '#6366F1',
                        },
                        boxShadow: {
                            glow: '0 0 0 2px rgba(56,189,248,.25), 0 0 40px rgba(20,184,166,.25)'
                        }
                    }
                }
            };
        }
    </script>
    
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        .htmx-indicator {
            display: none;
        }
        .htmx-request .htmx-indicator {
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gray-50">

