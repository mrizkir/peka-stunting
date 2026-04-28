import './bootstrap';
import Alpine from 'alpinejs';
import { createRegisterForm } from './forms/register-form';

window.Alpine = Alpine;
window.registerForm = createRegisterForm;

Alpine.start();
