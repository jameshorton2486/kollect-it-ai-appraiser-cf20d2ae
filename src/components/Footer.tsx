
export const Footer = () => {
  return (
    <footer className="border-t py-6 md:py-0">
      <div className="container flex h-14 items-center justify-between">
        <p className="text-sm text-muted-foreground">
          © 2025 Kollect-It Expert Appraiser. All rights reserved.
        </p>
        <nav className="flex gap-4 text-sm text-muted-foreground">
          <a href="/privacy" className="hover:underline">
            Privacy
          </a>
          <a href="/terms" className="hover:underline">
            Terms
          </a>
        </nav>
      </div>
    </footer>
  );
};
