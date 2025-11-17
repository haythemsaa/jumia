import Link from 'next/link';
import { FiFacebook, FiTwitter, FiInstagram, FiMail, FiPhone, FiMapPin } from 'react-icons/fi';

export default function Footer() {
  return (
    <footer className="bg-gray-900 text-white mt-auto">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* About */}
          <div>
            <h3 className="text-xl font-bold text-orange-500 mb-4">JUMIA</h3>
            <p className="text-gray-400 text-sm">
              La plateforme e-commerce n°1 en Tunisie. Trouvez tout ce dont vous avez besoin avec des millions de produits.
            </p>
            <div className="flex space-x-4 mt-4">
              <a href="#" className="hover:text-orange-500">
                <FiFacebook size={20} />
              </a>
              <a href="#" className="hover:text-orange-500">
                <FiTwitter size={20} />
              </a>
              <a href="#" className="hover:text-orange-500">
                <FiInstagram size={20} />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-semibold mb-4">Liens rapides</h4>
            <ul className="space-y-2 text-sm text-gray-400">
              <li>
                <Link href="/about" className="hover:text-orange-500">
                  À propos
                </Link>
              </li>
              <li>
                <Link href="/contact" className="hover:text-orange-500">
                  Contact
                </Link>
              </li>
              <li>
                <Link href="/faq" className="hover:text-orange-500">
                  FAQ
                </Link>
              </li>
              <li>
                <Link href="/vendor/register" className="hover:text-orange-500">
                  Devenir vendeur
                </Link>
              </li>
            </ul>
          </div>

          {/* Customer Service */}
          <div>
            <h4 className="font-semibold mb-4">Service client</h4>
            <ul className="space-y-2 text-sm text-gray-400">
              <li>
                <Link href="/help" className="hover:text-orange-500">
                  Centre d'aide
                </Link>
              </li>
              <li>
                <Link href="/returns" className="hover:text-orange-500">
                  Retours et remboursements
                </Link>
              </li>
              <li>
                <Link href="/shipping" className="hover:text-orange-500">
                  Livraison
                </Link>
              </li>
              <li>
                <Link href="/terms" className="hover:text-orange-500">
                  Conditions d'utilisation
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="font-semibold mb-4">Contactez-nous</h4>
            <ul className="space-y-3 text-sm text-gray-400">
              <li className="flex items-start space-x-2">
                <FiMapPin className="mt-1 flex-shrink-0" />
                <span>Centre Urbain Nord, Tunis, Tunisie</span>
              </li>
              <li className="flex items-center space-x-2">
                <FiPhone className="flex-shrink-0" />
                <span>+216 70 123 456</span>
              </li>
              <li className="flex items-center space-x-2">
                <FiMail className="flex-shrink-0" />
                <span>support@jumia.tn</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
          <p>&copy; {new Date().getFullYear()} JUMIA Tunisia. Tous droits réservés.</p>
        </div>
      </div>
    </footer>
  );
}
