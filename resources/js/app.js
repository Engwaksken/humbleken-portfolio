import './bootstrap';
import { initHeader } from './header';
import { initAccessibility } from './accessibility';
import { initChatbot } from './chatbot';
import { initPasswordToggles } from './password-toggle';
import { initReadMore } from './read-more';
import { initArticle } from './article';
import { initServicesAccordion } from './services-accordion';
import { initReveal } from './reveal';

initHeader();
// Before the effects below, so a saved "reduce motion" preference is already applied.
initAccessibility();
initChatbot();
initPasswordToggles();
initReadMore();
initArticle();
initServicesAccordion();
initReveal();
