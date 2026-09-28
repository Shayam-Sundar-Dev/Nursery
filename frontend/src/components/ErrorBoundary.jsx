import React from 'react';

export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error('Storefront View caught error:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="max-w-xl mx-auto my-16 p-8 text-center bg-white rounded-3xl border border-stone-200/80 shadow-card space-y-4">
          <div className="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto text-2xl font-bold">
            🌱
          </div>
          <h2 className="font-serif text-2xl font-bold text-stone-900">
            Greenhouse View Needs Attention
          </h2>
          <p className="text-stone-500 text-xs sm:text-sm">
            We encountered a temporary display issue with this section. You can return to our botanical home or reload.
          </p>
          <div className="pt-2 flex justify-center gap-3">
            <button
              onClick={() => {
                this.setState({ hasError: false });
                window.location.href = '/';
              }}
              className="h-11 px-5 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer"
            >
              Return Home
            </button>
            <button
              onClick={() => {
                this.setState({ hasError: false });
                window.location.reload();
              }}
              className="h-11 px-5 rounded-full border border-stone-300 hover:bg-stone-50 text-stone-700 font-bold text-xs uppercase tracking-wider transition cursor-pointer"
            >
              Reload Page
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
