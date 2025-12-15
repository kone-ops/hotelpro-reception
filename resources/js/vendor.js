/**
 * Vendor JS - Bootstrap, jQuery, DataTables
 * Ce fichier charge tous les assets vendors en local
 */

// jQuery (requis pour DataTables)
import $ from 'jquery';
window.jQuery = window.$ = $;

// Bootstrap 5
import 'bootstrap/dist/css/bootstrap.min.css';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css';

// Animate.css
import 'animate.css';

// DataTables
import 'datatables.net';
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

// DataTables Buttons
import 'datatables.net-buttons';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css';
import 'datatables.net-buttons/js/buttons.html5.js';
import 'datatables.net-buttons/js/buttons.print.js';
import 'datatables.net-buttons/js/buttons.colVis.js';

// DataTables Responsive
import 'datatables.net-responsive';
import 'datatables.net-responsive-bs5';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';

// JSZip (pour export Excel)
import JSZip from 'jszip';
window.JSZip = JSZip;

// PDFMake (pour export PDF)
import pdfMake from 'pdfmake/build/pdfmake';
import pdfFonts from 'pdfmake/build/vfs_fonts';
pdfMake.vfs = pdfFonts.pdfMake.vfs;
window.pdfMake = pdfMake;

console.log('✅ Vendors chargés en local (Bootstrap, jQuery, DataTables, etc.)');
