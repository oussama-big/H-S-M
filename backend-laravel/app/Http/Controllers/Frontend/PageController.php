<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return view('frontend.pages.home', ['title' => 'Accueil']);
    }

    public function about()
    {
        return view('frontend.pages.about', ['title' => 'A Propos']);
    }

    public function services()
    {
        return view('frontend.pages.services', ['title' => 'Nos Services']);
    }

    public function equipe()
    {
        return view('frontend.pages.equipe', ['title' => 'Notre Equipe']);
    }

    public function temoignages()
    {
        return view('frontend.pages.temoignages', ['title' => 'Temoignages']);
    }

    public function contact()
    {
        return view('frontend.pages.contact', ['title' => 'Rendez-vous']);
    }

    public function auth()
    {
        return view('frontend.auth.index', ['title' => 'Authentification']);
    }

    public function dashboard()
    {
        return view('frontend.dashboard', ['title' => 'Tableau de Bord']);
    }

    public function adminDashboard()
    {
        return view('admin.dashboard', ['title' => 'Dashboard Admin']);
    }

    public function profile()
    {
        return view('frontend.pages.profile', ['title' => 'Mon Profil']);
    }

    public function doctorDashboard(Request $request)
    {
        return view('frontend.doctor.dashboard', [
            'initialView' => $request->route('initialView', 'dashboard'),
            'title' => 'Dashboard Medecin',
        ]);
    }

    public function patientDashboard(Request $request)
    {
        return view('frontend.patient.dashboard', [
            'initialView' => $request->route('initialView', 'dashboard'),
            'title' => 'Dashboard Patient',
        ]);
    }

    public function secretaryDashboard(Request $request)
    {
        return view('frontend.secretary.dashboard', [
            'initialView' => $request->route('initialView', 'dashboard'),
            'title' => 'Dashboard Secretaire',
        ]);
    }

    public function submitContact(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|min:2|max:100',
            'telephone' => 'required|string|min:8|max:20',
            'email' => 'nullable|email|max:255',
            'specialite' => 'required|string',
            'date' => 'nullable|date|after_or_equal:today',
            'motif' => 'nullable|string|max:1000',
            'rgpd' => 'accepted',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'telephone.required' => 'Le telephone est obligatoire.',
            'specialite.required' => 'Veuillez choisir une specialite.',
            'rgpd.accepted' => 'Vous devez accepter notre politique de confidentialite.',
        ]);

        return redirect()
            ->route('contact')
            ->with('success', 'Votre demande de rendez-vous a bien ete envoyee. Nous vous contacterons dans les 24h.');
    }
}
