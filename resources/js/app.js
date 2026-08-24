import { Swiper } from 'swiper';
import { Autoplay, Pagination } from 'swiper/modules';
Swiper.use([Autoplay, Pagination]);
window.Swiper = Swiper;

import {
    Chart,
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js';
Chart.register(DoughnutController, ArcElement, Tooltip, Legend);
window.Chart = Chart;

import {
    createIcons,
    ArrowRight, BookX, Calendar, CalendarCheck, CalendarDays, Camera,
    Clock, Cross, Eye, FileText, MapPin, Phone, Search, Sparkles,
    Target, User, Wallet, BookOpen // Tambahan BookOpen dari halaman Renungan sebelumnya
} from 'lucide';

createIcons({
    icons: {
        ArrowRight, BookX, Calendar, CalendarCheck, CalendarDays, Camera,
        Clock, Cross, Eye, FileText, MapPin, Phone, Search, Sparkles,
        Target, User, Wallet, BookOpen
    },
});