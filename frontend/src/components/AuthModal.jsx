import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import {
  X,
  Lock,
  Mail,
  User,
  Sparkles,
  ArrowRight,
  ShieldCheck,
  AlertCircle,
  CheckCircle2,
} from 'lucide-react';

/**
 * Load Google Identity Services (GIS) script once.
 */
function loadGoogleScript() {
  if (document.getElementById('google-gis-script')) return;
  const s = document.createElement('script');
  s.id = 'google-gis-script';
  s.src = 'https://accounts.google.com/gsi/client';
  s.async = true;
  s.defer = true;
  document.head.appendChild(s);
}

export default function AuthModal() {
  const {
    authModalOpen,
    setAuthModalOpen,
    customerLogin,
    customerRegister,
    customerSocialLogin,
    siteSettings,
  } = useStore();

  const [mode, setMode] = useState('login'); // 'login' | 'register' | 'forgot'
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const [socialLoading, setSocialLoading] = useState(null);
  const [errorMessage, setErrorMessage] = useState('');
  const [forgotSent, setForgotSent] = useState(false);

  // Pull OAuth config from admin site settings
  const oauth = siteSettings?.oauth || {};
  const googleEnabled  = Boolean(oauth.google_oauth_enabled  && oauth.google_client_id);
  const githubEnabled  = Boolean(oauth.github_oauth_enabled  && oauth.github_client_id);
  const facebookEnabled = Boolean(oauth.facebook_oauth_enabled && oauth.facebook_app_id);
  const anyProviderEnabled = googleEnabled || githubEnabled || facebookEnabled;

  // Pre-load Google GIS script when Google is configured
  useEffect(() => {
    if (googleEnabled) loadGoogleScript();
  }, [googleEnabled]);

  // Sync mode when modal opens with a specific intent
  useEffect(() => {
    if (authModalOpen === 'login' || authModalOpen === 'register') {
      setMode(authModalOpen);
      setErrorMessage('');
      setForgotSent(false);
    }
  }, [authModalOpen]);

  if (!authModalOpen) return null;

  const handleClose = () => {
    setAuthModalOpen(null);
    setErrorMessage('');
    setForgotSent(false);
  };

  const handleEmailSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage('');
    setLoading(true);
    try {
      if (mode === 'login') {
        await customerLogin(email, password);
      } else {
        if (!name.trim()) {
          setErrorMessage('Please enter your full name.');
          setLoading(false);
          return;
        }
        await customerRegister(name, email, password);
      }
    } catch (err) {
      setErrorMessage(
        err.response?.data?.message ||
        err.message ||
        'Authentication failed. Please check your credentials.'
      );
    } finally {
      setLoading(false);
    }
  };

  const handleForgotSubmit = async (e) => {
    e.preventDefault();
    setErrorMessage('');
    setLoading(true);
    try {
      await api.customerForgotPassword(email);
      setForgotSent(true);
    } catch (err) {
      setErrorMessage(
        err.response?.data?.message ||
        err.message ||
        'Unable to send reset link. Please try again.'
      );
    } finally {
      setLoading(false);
    }
  };

  /** Real Google OAuth via GIS popup */
  const handleGoogleOAuth = () => {
    setSocialLoading('google');
    setErrorMessage('');

    if (!window.google || !window.google.accounts) {
      setTimeout(() => handleGoogleOAuth(), 500);
      return;
    }

    const client = window.google.accounts.oauth2.initTokenClient({
      client_id: oauth.google_client_id,
      scope: 'openid email profile',
      callback: async (response) => {
        if (response.error) {
          setSocialLoading(null);
          setErrorMessage('Google sign-in was cancelled or failed.');
          return;
        }
        try {
          const profileRes = await fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
            headers: { Authorization: `Bearer ${response.access_token}` },
          });
          const profile = await profileRes.json();
          await customerSocialLogin('google', {
            name: profile.name,
            email: profile.email,
            avatar: profile.picture,
            provider_id: profile.sub,
            access_token: response.access_token,
          });
        } catch (err) {
          setErrorMessage(err.message || 'Google sign-in failed.');
        } finally {
          setSocialLoading(null);
        }
      },
    });
    client.requestAccessToken();
  };

  /** GitHub OAuth — redirect flow */
  const handleGithubOAuth = () => {
    setSocialLoading('github');
    setErrorMessage('');
    const params = new URLSearchParams({
      client_id: oauth.github_client_id,
      scope: 'user:email read:user',
      redirect_uri: `${window.location.origin}/api/v1/auth/social/github/callback`,
      state: btoa(window.location.origin),
    });
    window.location.href = `https://github.com/login/oauth/authorize?${params}`;
  };

  /** Facebook Login SDK */
  const handleFacebookOAuth = () => {
    setSocialLoading('facebook');
    setErrorMessage('');

    if (!window.FB) {
      window.fbAsyncInit = function () {
        window.FB.init({
          appId: oauth.facebook_app_id,
          cookie: true,
          xfbml: true,
          version: 'v19.0',
        });
        triggerFBLogin();
      };
      const s = document.createElement('script');
      s.src = 'https://connect.facebook.net/en_US/sdk.js';
      s.async = true;
      document.head.appendChild(s);
      return;
    }
    triggerFBLogin();
  };

  const triggerFBLogin = () => {
    window.FB.login(async (response) => {
      if (response.authResponse) {
        try {
          const profileRes = await new Promise((resolve) =>
            window.FB.api('/me', { fields: 'name,email,picture' }, resolve)
          );
          await customerSocialLogin('facebook', {
            name: profileRes.name,
            email: profileRes.email,
            avatar: profileRes.picture?.data?.url,
            provider_id: response.authResponse.userID,
            access_token: response.authResponse.accessToken,
          });
        } catch (err) {
          setErrorMessage(err.message || 'Facebook sign-in failed.');
        } finally {
          setSocialLoading(null);
        }
      } else {
        setSocialLoading(null);
        setErrorMessage('Facebook sign-in was cancelled.');
      }
    }, { scope: 'public_profile,email' });
  };

  const handleSocialClick = (provider) => {
    if (provider === 'google')   return handleGoogleOAuth();
    if (provider === 'github')   return handleGithubOAuth();
    if (provider === 'facebook') return handleFacebookOAuth();
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 animate-in fade-in duration-200">
      <div className="p-2 rounded-[2.25rem] bg-stone-800/20 backdrop-blur-md border border-white/20 shadow-2xl max-w-md w-full">
        <div className="relative bg-white rounded-[1.85rem] border border-stone-200/90 w-full overflow-hidden transform transition-all">
          {/* Header Ribbon */}
          <div className="bg-gradient-to-r from-botanical-800 to-botanical-900 text-white p-6 sm:p-7 relative">
            <button
              onClick={handleClose}
              className="absolute top-5 right-5 p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition active:scale-95"
              aria-label="Close dialog"
            >
              <X className="w-4 h-4" />
            </button>

            <div className="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-botanical-700/60 border border-botanical-500/30 text-botanical-200 text-xs font-semibold mb-3 shadow-2xs">
              <Sparkles className="w-3.5 h-3.5 text-amber-400" />
              <span>Nursery Garden Membership</span>
            </div>

            <h2 className="font-serif text-2xl font-bold text-white tracking-tight">
              {mode === 'login'
                ? 'Welcome Back, Gardener'
                : mode === 'register'
                ? 'Join Our Botanical Sanctuary'
                : 'Reset Your Password'}
            </h2>
            <p className="text-botanical-200 text-xs mt-1 leading-relaxed">
              {mode === 'login'
                ? 'Sign in to access your live plant orders, wishlist & garden care alerts.'
                : mode === 'register'
                ? 'Create an account to track shipments, save favorites & receive care reminders.'
                : 'Enter your registered email address to receive password recovery instructions.'}
            </p>

            {/* Navigation / Mode Switch */}
            {mode === 'forgot' ? (
              <div className="flex items-center justify-between mt-4 pt-2 border-t border-botanical-700/40">
                <button
                  type="button"
                  onClick={() => { setMode('login'); setErrorMessage(''); setForgotSent(false); }}
                  className="text-xs font-semibold text-botanical-200 hover:text-white transition flex items-center gap-1.5"
                >
                  &larr; Back to Sign In
                </button>
                <span className="text-[10px] uppercase font-bold tracking-wider text-botanical-300 bg-botanical-950/40 px-2.5 py-0.5 rounded-full">
                  Account Recovery
                </span>
              </div>
            ) : (
              <div className="flex bg-botanical-950/40 p-1.5 rounded-full mt-5 border border-botanical-700/40 shadow-inner">
                <button
                  type="button"
                  onClick={() => { setMode('login'); setErrorMessage(''); setForgotSent(false); }}
                  className={`flex-1 py-1.5 text-xs font-semibold rounded-full transition ${
                    mode === 'login' ? 'bg-white text-botanical-950 shadow-xs' : 'text-botanical-200 hover:text-white'
                  }`}
                >
                  Sign In
                </button>
                <button
                  type="button"
                  onClick={() => { setMode('register'); setErrorMessage(''); setForgotSent(false); }}
                  className={`flex-1 py-1.5 text-xs font-semibold rounded-full transition ${
                    mode === 'register' ? 'bg-white text-botanical-950 shadow-xs' : 'text-botanical-200 hover:text-white'
                  }`}
                >
                  Create Account
                </button>
              </div>
            )}
          </div>

        {/* Content Body */}
        <div className="p-6 sm:p-7 space-y-5">
          {/* Error Banner */}
          {errorMessage && (
            <div className="p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2.5">
              <AlertCircle className="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
              <div className="leading-snug">{errorMessage}</div>
            </div>
          )}

          {/* 1. FORGOT PASSWORD VIEW */}
          {mode === 'forgot' ? (
            <div>
              {forgotSent ? (
                <div className="text-center py-4 space-y-4">
                  <div className="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto shadow-xs">
                    <CheckCircle2 className="w-6 h-6" />
                  </div>
                  <div>
                    <h3 className="font-serif text-lg font-bold text-stone-900">
                      Recovery Link Sent!
                    </h3>
                    <p className="text-xs text-stone-500 mt-1.5 max-w-xs mx-auto leading-relaxed">
                      If an account is associated with <span className="font-semibold text-stone-800">{email}</span>, a secure password reset link has been dispatched to your email.
                    </p>
                  </div>
                  <button
                    type="button"
                    onClick={() => { setMode('login'); setForgotSent(false); setErrorMessage(''); }}
                    className="w-full py-3 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs transition"
                  >
                    Return to Sign In
                  </button>
                </div>
              ) : (
                <form onSubmit={handleForgotSubmit} className="space-y-4">
                  <p className="text-xs text-stone-500 leading-relaxed">
                    Forgot your password? Enter your registered email address and we'll send you instructions to set a new password.
                  </p>

                  <div>
                    <label className="block text-xs font-semibold text-stone-700 mb-1">
                      Email Address
                    </label>
                    <div className="relative">
                      <input
                        type="email"
                        required
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="gardener@example.com"
                        className="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-stone-200 focus:border-botanical-600 focus:ring-2 focus:ring-botanical-600/20 text-xs font-medium focus:outline-none transition"
                      />
                      <Mail className="w-4 h-4 text-stone-400 absolute left-3 top-3" />
                    </div>
                  </div>

                  <button
                    type="submit"
                    disabled={loading}
                    className="relative group inline-flex items-center justify-between w-full p-1.5 pl-5 pr-1.5 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-98 text-white font-bold text-xs shadow-md shadow-botanical-900/20 transition disabled:opacity-50"
                  >
                    <span>Send Password Reset Link</span>
                    <span className="w-8 h-8 rounded-full bg-white/10 group-hover:bg-white/20 flex items-center justify-center transition">
                      {loading ? (
                        <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      ) : (
                        <ArrowRight className="w-4 h-4 text-white" />
                      )}
                    </span>
                  </button>

                  <button
                    type="button"
                    onClick={() => { setMode('login'); setErrorMessage(''); }}
                    className="w-full text-center text-xs text-stone-500 hover:text-stone-800 font-semibold transition py-1"
                  >
                    &larr; Remember your password? Sign In
                  </button>
                </form>
              )}
            </div>
          ) : (
            /* 2. SIGN IN & REGISTER VIEW */
            <>
              {/* Social Login Section — only shown if at least one provider is enabled */}
              {anyProviderEnabled && (
                <div>
                  <div className="text-[11px] font-bold uppercase tracking-wider text-stone-400 mb-2.5">
                    Quick Social Sign-In
                  </div>
                  <div className={`grid gap-2.5 ${
                    [googleEnabled, githubEnabled, facebookEnabled].filter(Boolean).length === 1
                      ? 'grid-cols-1'
                      : [googleEnabled, githubEnabled, facebookEnabled].filter(Boolean).length === 2
                      ? 'grid-cols-2'
                      : 'grid-cols-3'
                  }`}>
                    {/* Google */}
                    {googleEnabled && (
                      <button
                        type="button"
                        onClick={() => handleSocialClick('google')}
                        disabled={Boolean(socialLoading) || loading}
                        className="flex items-center justify-center gap-2 px-3 py-2.5 border border-stone-200 rounded-xl hover:bg-stone-50 hover:border-stone-300 transition text-xs font-semibold text-stone-700 shadow-2xs group disabled:opacity-50"
                        title="Continue with Google"
                      >
                        {socialLoading === 'google' ? (
                          <div className="w-4 h-4 border-2 border-stone-400 border-t-botanical-600 rounded-full animate-spin" />
                        ) : (
                          <svg className="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                          </svg>
                        )}
                        <span>Google</span>
                      </button>
                    )}

                    {/* GitHub */}
                    {githubEnabled && (
                      <button
                        type="button"
                        onClick={() => handleSocialClick('github')}
                        disabled={Boolean(socialLoading) || loading}
                        className="flex items-center justify-center gap-2 px-3 py-2.5 border border-stone-200 rounded-xl hover:bg-stone-50 hover:border-stone-300 transition text-xs font-semibold text-stone-700 shadow-2xs disabled:opacity-50"
                        title="Continue with GitHub"
                      >
                        {socialLoading === 'github' ? (
                          <div className="w-4 h-4 border-2 border-stone-400 border-t-botanical-600 rounded-full animate-spin" />
                        ) : (
                          <svg className="w-4 h-4 fill-current text-stone-900" viewBox="0 0 24 24">
                            <path fillRule="evenodd" clipRule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                          </svg>
                        )}
                        <span>GitHub</span>
                      </button>
                    )}

                    {/* Facebook */}
                    {facebookEnabled && (
                      <button
                        type="button"
                        onClick={() => handleSocialClick('facebook')}
                        disabled={Boolean(socialLoading) || loading}
                        className="flex items-center justify-center gap-2 px-3 py-2.5 border border-stone-200 rounded-xl hover:bg-stone-50 hover:border-stone-300 transition text-xs font-semibold text-stone-700 shadow-2xs disabled:opacity-50"
                        title="Continue with Facebook"
                      >
                        {socialLoading === 'facebook' ? (
                          <div className="w-4 h-4 border-2 border-stone-400 border-t-botanical-600 rounded-full animate-spin" />
                        ) : (
                          <svg className="w-4 h-4" viewBox="0 0 24 24">
                            <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                          </svg>
                        )}
                        <span>Facebook</span>
                      </button>
                    )}
                  </div>
                </div>
              )}

              {/* Divider */}
              {anyProviderEnabled && (
                <div className="relative flex items-center justify-center">
                  <div className="border-t border-stone-200 w-full" />
                  <span className="bg-white px-3 text-[11px] font-semibold text-stone-400 uppercase tracking-wider">
                    Or with email
                  </span>
                </div>
              )}

              {/* Email/Password Form */}
              <form onSubmit={handleEmailSubmit} className="space-y-3.5">
                {mode === 'register' && (
                  <div>
                    <label className="block text-xs font-semibold text-stone-700 mb-1">Full Name</label>
                    <div className="relative">
                      <input
                        type="text"
                        required
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        placeholder="e.g. Vikramaditya Rao"
                        className="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-stone-200 focus:border-botanical-600 focus:ring-2 focus:ring-botanical-600/20 text-xs font-medium focus:outline-none transition"
                      />
                      <User className="w-4 h-4 text-stone-400 absolute left-3 top-3" />
                    </div>
                  </div>
                )}

                <div>
                  <label className="block text-xs font-semibold text-stone-700 mb-1">Email Address</label>
                  <div className="relative">
                    <input
                      type="email"
                      required
                      value={email}
                      onChange={(e) => setEmail(e.target.value)}
                      placeholder="gardener@example.com"
                      className="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-stone-200 focus:border-botanical-600 focus:ring-2 focus:ring-botanical-600/20 text-xs font-medium focus:outline-none transition"
                    />
                    <Mail className="w-4 h-4 text-stone-400 absolute left-3 top-3" />
                  </div>
                </div>

                <div>
                  <div className="flex items-center justify-between mb-1">
                    <label className="block text-xs font-semibold text-stone-700">
                      Password {mode === 'register' && <span className="text-[10px] text-stone-400 font-normal">(min 8 characters)</span>}
                    </label>
                    {mode === 'login' && (
                      <button
                        type="button"
                        onClick={() => { setMode('forgot'); setErrorMessage(''); setForgotSent(false); }}
                        className="text-[11px] font-semibold text-botanical-700 hover:text-botanical-900 hover:underline transition"
                      >
                        Forgot password?
                      </button>
                    )}
                  </div>
                  <div className="relative">
                    <input
                      type="password"
                      required
                      minLength={mode === 'register' ? 8 : 1}
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="••••••••"
                      className="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-stone-200 focus:border-botanical-600 focus:ring-2 focus:ring-botanical-600/20 text-xs font-medium focus:outline-none transition"
                    />
                    <Lock className="w-4 h-4 text-stone-400 absolute left-3 top-3" />
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={loading || Boolean(socialLoading)}
                  className="relative group inline-flex items-center justify-between w-full mt-2 p-1.5 pl-5 pr-1.5 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-98 text-white font-bold text-xs shadow-md shadow-botanical-900/20 transition disabled:opacity-50"
                >
                  <span>{mode === 'login' ? 'Sign In to Account' : 'Complete Registration'}</span>
                  <span className="w-8 h-8 rounded-full bg-white/10 group-hover:bg-white/20 flex items-center justify-center transition">
                    {loading ? (
                      <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                    ) : (
                      <ArrowRight className="w-4 h-4 text-white" />
                    )}
                  </span>
                </button>
              </form>
            </>
          )}

          {/* Privacy note */}
          <div className="flex items-center gap-2 text-[11px] text-stone-400 bg-sand-50/60 p-2.5 rounded-2xl border border-stone-100">
            <ShieldCheck className="w-4 h-4 text-botanical-600 shrink-0" />
            <span>Encrypted with Laravel Sanctum tokens. Living plant guarantee attached.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  );
}
