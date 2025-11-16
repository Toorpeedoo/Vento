import { NextResponse } from 'next/server';

export async function GET() {
  const diagnostics = {
    timestamp: new Date().toISOString(),
    environment: process.env.NODE_ENV,
    vercel: !!process.env.VERCEL,
    checks: {
      mongodb_uri: {
        set: !!process.env.MONGODB_URI,
        valid: process.env.MONGODB_URI ? process.env.MONGODB_URI.startsWith('mongodb') : false,
      },
      mongodb_db: {
        set: !!process.env.MONGODB_DB,
        value: process.env.MONGODB_DB || 'not set',
      },
      jwt_secret: {
        set: !!process.env.JWT_SECRET,
        using_default: process.env.JWT_SECRET === 'vento-secret-key-change-in-production-2024',
      },
      nextauth_secret: {
        set: !!process.env.NEXTAUTH_SECRET,
        using_default: process.env.NEXTAUTH_SECRET === 'vento-nextauth-secret-change-in-production-2024',
      },
      nextauth_url: {
        set: !!process.env.NEXTAUTH_URL,
        value: process.env.NEXTAUTH_URL || 'not set',
      },
    },
    issues: [] as string[],
  };

  // Check for issues
  if (!diagnostics.checks.mongodb_uri.set) {
    diagnostics.issues.push('MONGODB_URI is not set');
  }
  if (!diagnostics.checks.mongodb_uri.valid) {
    diagnostics.issues.push('MONGODB_URI does not start with "mongodb"');
  }
  if (!diagnostics.checks.jwt_secret.set && !diagnostics.checks.nextauth_secret.set) {
    diagnostics.issues.push('Neither JWT_SECRET nor NEXTAUTH_SECRET is set');
  }
  if (diagnostics.checks.jwt_secret.using_default || diagnostics.checks.nextauth_secret.using_default) {
    diagnostics.issues.push('Using default secret (security risk in production)');
  }

  const hasIssues = diagnostics.issues.length > 0;

  return NextResponse.json(
    {
      status: hasIssues ? 'error' : 'ok',
      message: hasIssues 
        ? 'Configuration issues detected' 
        : 'All environment variables are properly configured',
      ...diagnostics,
    },
    { status: hasIssues ? 500 : 200 }
  );
}
