-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 08:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `saththar_feeds_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@saththarfeeds.com', 'admin123', '2025-06-13 06:49:00');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 2, 'Invoice generated for order #3', '2025-07-19 18:08:53'),
(2, 2, 'Invoice generated for order #3', '2025-07-19 18:11:46'),
(3, 2, 'Invoice generated for order #3', '2025-07-19 18:31:01'),
(4, 2, 'Invoice generated for order #3', '2025-07-19 18:35:18'),
(5, 2, 'Invoice generated for order #3', '2025-07-19 18:38:25'),
(6, 2, 'Invoice generated for order #3', '2025-07-19 18:41:16'),
(7, 2, 'Invoice generated for order #3', '2025-07-19 19:26:18'),
(8, 2, 'Invoice generated for order #3', '2025-07-19 19:33:09'),
(9, 2, 'Invoice generated for order #3', '2025-07-19 19:38:35'),
(10, 2, 'Invoice generated for order #3', '2025-07-19 19:47:54'),
(11, 2, 'Invoice generated for order #3', '2025-07-19 19:49:06'),
(12, 2, 'Invoice generated for order #3', '2025-07-19 19:50:38'),
(13, 2, 'Invoice generated for order #3', '2025-07-19 19:51:39'),
(14, 2, 'Invoice generated for order #3', '2025-07-19 20:00:19'),
(15, 2, 'Invoice generated for order #3', '2025-07-19 20:00:47'),
(16, 2, 'Invoice generated for order #3', '2025-07-19 20:07:46'),
(17, 2, 'Invoice generated for order #3', '2025-07-19 20:24:21'),
(18, 2, 'Invoice generated for order #3', '2025-07-19 20:39:15'),
(19, 2, 'Invoice generated for order #3', '2025-07-19 20:44:45'),
(20, 2, 'Invoice generated for order #3', '2025-07-19 20:47:44'),
(21, 2, 'Invoice generated for order #3', '2025-07-19 20:56:21'),
(22, 2, 'Invoice generated for order #3', '2025-07-19 21:00:09'),
(23, 2, 'Invoice generated for order #3', '2025-07-19 21:01:26'),
(24, 2, 'Invoice generated for order #3', '2025-07-19 21:01:44'),
(25, 2, 'Invoice generated for order #3', '2025-07-19 21:04:36'),
(26, 2, 'Invoice generated for order #3', '2025-07-19 21:05:31'),
(27, 2, 'Invoice generated for order #4', '2025-07-19 21:07:07'),
(28, 2, 'Invoice generated for order #4', '2025-07-19 21:08:51'),
(29, 2, 'Invoice generated for order #5', '2025-07-19 21:09:49'),
(30, 2, 'Invoice generated for order #5', '2025-07-19 21:10:03'),
(31, 2, 'Invoice generated for order #5', '2025-07-19 21:10:30'),
(32, 2, 'Invoice generated for order #4', '2025-07-19 21:10:40'),
(33, 2, 'Invoice generated for order #3', '2025-07-19 21:10:50'),
(34, 2, 'Invoice generated for order #5', '2025-07-19 21:16:12'),
(35, 2, 'Invoice generated for order #4', '2025-07-19 21:17:28'),
(36, 2, 'Invoice generated for order #6', '2025-07-19 21:18:36'),
(37, 2, 'Invoice generated for order #6', '2025-07-19 21:19:13'),
(38, 2, 'Invoice generated for order #5', '2025-07-19 21:19:19'),
(39, 2, 'Invoice generated for order #4', '2025-07-19 21:19:24'),
(40, 2, 'Invoice generated for order #6', '2025-07-19 21:22:56'),
(41, 2, 'Invoice generated for order #5', '2025-07-19 21:23:01'),
(42, 2, 'Invoice generated for order #6', '2025-07-19 21:23:14'),
(43, 2, 'Invoice generated for order #5', '2025-07-19 21:23:20'),
(44, 2, 'Invoice generated for order #6', '2025-07-19 21:28:47'),
(45, 2, 'Invoice generated for order #5', '2025-07-19 21:30:34'),
(46, 2, 'Invoice generated for order #4', '2025-07-19 21:31:19'),
(47, 2, 'Invoice generated for order #6', '2025-07-19 21:31:42'),
(48, 2, 'Invoice generated for order #6', '2025-07-19 21:31:45'),
(49, 2, 'Invoice generated for order #6', '2025-07-19 21:33:04'),
(50, 2, 'Invoice generated for order #7', '2025-07-20 11:20:01'),
(51, 2, 'Invoice generated for order #7', '2025-07-20 13:24:41'),
(52, 2, 'Invoice generated for order #8', '2025-07-20 13:43:33');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `pet_type` varchar(50) DEFAULT NULL,
  `pet_age` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `image`, `created_at`, `description`, `stock`, `pet_type`, `pet_age`) VALUES
(1, 'Calf Milk Replacer', 4500.00, './Uploads/products/1752917088_SwiftStart_CalfMilkReplacer-removebg-preview.png', '2025-07-19 08:29:18', 'Calf Milk Replacer (CMR) is a specially formulated powdered milk substitute designed to provide essential nutrition to young calves from birth up to weaning (typically 0–8 weeks). It contains a balanced mix of high-quality proteins, fats, vitamins, and minerals that support optimal growth, immune development, and digestive health.', 45, 'Cow', 'Calf (0–6 months)'),
(2, 'Vitamin ADE', 3000.00, './Uploads/products/1752915838_2975-VITAMIN-ADE-INJ_880x726.png', '2025-07-19 08:31:12', 'Vitamin A, D, and E (ADE) supplements are essential fat-soluble vitamins used to support the immune system, bone development, vision, and reproductive health in animals such as cattle, goats, sheep, poultry, and pets.', 44, 'Cow', 'Calf (0–6 months)'),
(3, 'Forage-Based Pellets', 5500.00, './Uploads/products/1752915679_bos-6.png', '2025-07-19 09:01:19', 'Forage-based pellets are a highly digestible, fiber-rich feed supplement designed to support the healthy growth and development of young bulls and heifers. These pellets are composed mainly of high-quality forages (like alfalfa, grass hay, or legume meal), combined with essential vitamins, minerals, and sometimes protein sources to provide a complete and balanced diet.', 45, 'Cow', 'Heifer/Young Bull (6–24 months)'),
(4, 'Calcium', 800.00, './Uploads/products/1752917027_64635e3190f_3510781_1_large-removebg-preview.png', '2025-07-19 09:03:01', 'Calcium supplements are essential for supporting strong bone development, muscle function, and overall growth in young bulls. During the growth phase (especially between 6 to 24 months), adequate calcium intake is crucial to prevent skeletal deformities and ensure optimal weight gain and structural strength.', 45, 'Cow', 'Heifer/Young Bull (6–24 months)'),
(5, 'Forage-Based Pellets', 6000.00, './Uploads/products/1752916106_bos-6.png', '2025-07-19 09:08:26', 'Forage-based pellets are a highly digestible, fiber-rich feed supplement designed to support the healthy growth and development of young bulls and heifers. These pellets are composed mainly of high-quality forages (like alfalfa, grass hay, or legume meal), combined with essential vitamins, minerals, and sometimes protein sources to provide a complete and balanced diet.', 50, 'Cow', 'Mature Cow/Bull (2–8 years)'),
(6, 'Vitamin B12', 1800.00, './Uploads/products/1752916162_VITAMIN-B12_100ML_AUST_CD1226V2.png', '2025-07-19 09:09:22', 'Vitamin B12 (Cobalamin) is a vital water-soluble vitamin that plays a key role in energy metabolism, red blood cell formation, and nervous system health in mature cattle. It\'s particularly important in animals fed on low-cobalt soils or high-forage diets, where natural synthesis by rumen microbes may be inadequate.', 24, 'Cow', 'Mature Cow/Bull (2–8 years)'),
(7, 'Senior Cattle Pellets', 5800.00, './Uploads/products/1752916366_cattle-feed-pellets.jpeg', '2025-07-19 09:12:46', 'Senior cattle pellets are specially formulated to meet the nutritional needs of aging cows. As cows age, their digestion efficiency, nutrient absorption, and energy requirements change. These pellets are designed to support joint health, maintain body condition, improve immunity, and prevent age-related decline in productivity or health.', 18, 'Cow', 'Senior (8+ years)'),
(8, 'Vitamin C', 1200.00, './Uploads/products/1752916464_VITAMIN-C_100ML_AU_CD1231V2_PS.png', '2025-07-19 09:14:24', 'Vitamin C (ascorbic acid) is a powerful antioxidant that helps support immune function, reduce inflammation, and combat stress in aging cows. Although ruminants can synthesize Vitamin C in the liver, older cows often produce less, especially during stress, illness, or extreme heat. Supplementation helps boost their resilience and overall health.', 50, 'Cow', 'Senior (8+ years)'),
(9, 'Foal Milk Replacer', 4500.00, './Uploads/products/1752917007_2243_5139-removebg-preview.png', '2025-07-19 09:16:41', 'Foal Milk Replacer is a scientifically formulated milk substitute designed to closely mimic mare’s milk. It is used for orphaned, rejected, or underfed foals, or when the mare is not producing enough milk. This formula provides balanced nutrition essential for healthy growth, immune support, and digestive development in foals from birth up to 3–4 months, with gradual weaning thereafter.', 50, 'Horse', 'Foal (0–6 months)'),
(10, 'Timothy Pellets', 7000.00, './Uploads/products/1752917723_80390_Standlee_Alfalfa-Timothy-Pellets_webimg1-removebg-preview.png', '2025-07-19 09:32:06', 'Timothy pellets are a high-fiber, low-protein forage feed made from sun-cured Timothy grass compressed into easy-to-feed pellets. They provide consistent, dust-free roughage suitable for growing yearlings and young horses, especially those sensitive to alfalfa or rich pasture. These pellets support digestive health, weight gain, and proper skeletal development without excess calories or protein.', 48, 'Horse', 'Yearling (6–24 months)'),
(11, 'Zinc', 1200.00, './Uploads/products/1752917659_V8814HestevardZinc_750g_v13_0001.webp', '2025-07-19 09:34:19', 'Zinc is an essential trace mineral that plays a vital role in growth, immune function, skin and hoof health, and enzyme activity in growing horses. Yearlings, especially those in early training or with high growth demands, may require additional zinc beyond what is present in forage-based diets.', 44, 'Horse', 'Yearling (6–24 months)'),
(12, 'Performance Horse Pellets', 7500.00, './Uploads/products/1752941258_KBS-InspirePEAK-Performance14Pellet_1500x1500-2-removebg-preview.png', '2025-07-19 16:07:38', 'Performance Horse Pellets are a high-energy, nutrient-dense feed developed to meet the elevated demands of adult horses in work or competition. These pellets provide a balanced blend of energy, protein, vitamins, and minerals to support muscle development, stamina, recovery, and overall condition.', 20, 'Horse', 'Adult Horse (2–15 years)'),
(13, 'Vitamin E', 3500.00, './Uploads/products/1752941426_VitaminE_500g_700.png', '2025-07-19 16:10:26', 'Vitamin E is a powerful antioxidant essential for muscle function, nerve health, and immune response in adult horses. Horses in training, performance, or breeding programs often have elevated Vitamin E requirements—especially if they consume mostly hay (which loses vitamin E content during storage) or live in areas with limited fresh pasture.', 8, 'Horse', 'Adult Horse (2–15 years)'),
(14, 'Senior Horse Pellets', 8500.00, './Uploads/products/1752941626_CG789_EV_Senior_product-card-zoom_1536x1844-removebg-preview.png', '2025-07-19 16:13:46', 'Senior Horse Pellets are a specialized, complete feed formulated to meet the unique nutritional needs of aging horses (15+ years). As horses grow older, they often face challenges such as dental wear, slower digestion, weight loss, and reduced nutrient absorption. These pellets are designed to be highly palatable, easy to chew, and digestible, ensuring older horses maintain optimal condition, energy, and health.', 13, 'Horse', 'Senior (15+ years)'),
(15, 'Vitamin C', 3200.00, './Uploads/products/1752941784_VitaminC_1kg_700.png', '2025-07-19 16:16:24', 'Vitamin C (ascorbic acid) is a crucial antioxidant that helps support immune function, joint health, and tissue repair in horses. While healthy horses typically synthesize Vitamin C in the liver, aging horses (15+ years) may have reduced natural production and greater oxidative stress, making supplementation beneficial—especially during illness, injury, high-stress periods, or in tropical climates like Sri Lanka.', 12, 'Horse', 'Senior (15+ years)'),
(16, 'Lamb Milk Replacer', 2500.00, './Uploads/products/1752942079_81ZoPZBlj+L._UF894_1000_QL80_-removebg-preview.png', '2025-07-19 16:21:19', 'Lamb Milk Replacer is a specially formulated, high-nutrient powdered milk substitute designed to support the healthy growth and immunity of orphaned, rejected, or supplementary-fed lambs (0–6 months). It mimics the nutritional composition of ewe’s milk and provides essential proteins, fats, vitamins, and minerals necessary for early-stage development.\r\n\r\n', 23, 'Sheep', 'Lamb (0–6 months)'),
(17, 'Vitamin A', 1200.00, './Uploads/products/1752942254_2202323-removebg-preview.png', '2025-07-19 16:24:14', 'Vitamin A is an essential fat-soluble vitamin critical for the growth, immunity, and vision of lambs. Young lambs—especially those reared without access to ewe’s milk or green forage—may require Vitamin A supplementation to prevent growth delays, respiratory infections, and night blindness.', 25, 'Sheep', 'Lamb (0–6 months)'),
(18, 'Alfalfa Pellets', 3800.00, './Uploads/products/1752942470_Standlee-Hay-40-lbs-Premium-Organic-Alfalfa-Pellets_73b61a91-03e8-4da9-b7df-5af5a63c3a9d.89ef80ee78cfbca491635d8dba3353b6-removebg-preview.png', '2025-07-19 16:27:50', 'Alfalfa Pellets are a nutrient-dense, high-protein forage feed made from sun-cured alfalfa hay, compressed into easy-to-handle pellets. They are ideal for hoggets (6–12 months) undergoing rapid growth, muscle development, or needing extra energy during transitional stages such as weaning or pre-breeding.', 25, 'Sheep', 'Hogget (6–12 months)'),
(19, 'Copper', 2000.00, './Uploads/products/1752942633_PVT0134-removebg-preview.png', '2025-07-19 16:30:33', 'Copper is a vital trace mineral required for the growth, immunity, reproduction, pigmentation, and enzyme function in hoggets. Copper vitamins help ensure hoggets develop strong bones, healthy wool, and a robust immune system. Supplementation is essential where forage or soil is copper-deficient or antagonistic elements (like molybdenum, sulfur, or iron) are present.', 24, 'Sheep', 'Hogget (6–12 months)'),
(20, 'Alfalfa Pellets', 7800.00, './Uploads/products/1752942961_R1176-Ooi-en-lam-Bag-Mu-removebg-preview.png', '2025-07-19 16:36:01', 'Alfalfa Pellets are made from sun-cured, chopped alfalfa hay compressed into easy-to-feed pellets. These pellets are a rich source of protein, fiber, vitamins, and minerals, specially suited for breeding-age ewes and rams (1–6 years), whether for maintenance, gestation, lactation, or pre-breeding conditioning.', 10, 'Sheep', 'Ewe/Ram (1–6 years)'),
(21, 'Vitamin B1', 1800.00, './Uploads/products/1752943066_1-ltr-removebg-preview.png', '2025-07-19 16:37:46', 'Vitamin B1, also known as Thiamine, is an essential water-soluble vitamin critical for energy metabolism, nerve function, and overall health in sheep. Adequate Vitamin B1 supports the nervous system, aids in converting carbohydrates into energy, and helps prevent neurological disorders such as polioencephalomalacia (PEM), which can occur due to thiamine deficiency or ruminal disturbances.', 51, 'Sheep', 'Ewe/Ram (1–6 years)'),
(22, 'Soft Feed Pellets', 1800.00, './Uploads/products/1752943313_5414365352646_prd_webl-removebg-preview.png', '2025-07-19 16:41:53', 'Soft Feed Pellets are specially designed, highly digestible pellets tailored for senior sheep aged 6 years and above. As sheep age, they may experience dental problems, reduced chewing efficiency, and slower digestion. These pellets are soft-textured and nutrient-rich to ensure easy consumption, optimal nutrient absorption, and maintenance of body condition in older sheep.', 50, 'Sheep', 'Senior (6+ years)'),
(23, 'Vitamin C', 2800.00, './Uploads/products/1752943504_Vitamin-C-removebg-preview.png', '2025-07-19 16:45:04', 'Vitamin C (ascorbic acid) is a vital antioxidant that supports immune function, tissue repair, and overall health in senior sheep. Although sheep can synthesize Vitamin C naturally, older sheep (6+ years) may benefit from supplementation during stress, illness, or poor nutrition to help combat oxidative damage and maintain vitality.', 26, 'Sheep', 'Senior (6+ years)'),
(24, 'Vitamin D ', 2500.00, './Uploads/products/1752943765_naf-equine-mare-foal-youngstock-powder-supplement-1-8-kg-1130301595-removebg-preview.png', '2025-07-19 16:49:25', 'Vitamin D is a crucial fat-soluble vitamin that plays a key role in calcium and phosphorus metabolism, essential for healthy bone growth and development in foals. Adequate Vitamin D supports proper skeletal formation, prevents rickets, and ensures strong teeth during the rapid growth phase of foals.', 10, 'Horse', 'Foal (0–6 months)'),
(25, 'Kid Milk Replacer', 2500.00, './Uploads/products/1752943882_61RaqQQCMAL._UF894_1000_QL80_-removebg-preview.png', '2025-07-19 16:51:22', 'Kid Milk Replacer is a specially formulated powdered milk substitute designed to provide complete nutrition for young goat kids aged 0–6 months. It closely mimics the composition of natural goat milk, supplying essential proteins, fats, vitamins, and minerals needed for healthy growth, immune development, and digestive health, especially for orphaned, rejected, or supplementary-fed kids.', 28, 'Goat', 'Kid (0–6 months)'),
(26, 'Vitamin A', 3500.00, './Uploads/products/1752944006_Product_Goat_Purina_Goat-Grower.webp', '2025-07-19 16:53:26', 'Vitamin A is an essential fat-soluble vitamin crucial for the growth, immune function, and vision development of young goat kids. It supports healthy skin, mucous membranes, and overall organ function. Young kids, especially those on artificial feeding or poor-quality forage, may need Vitamin A supplementation to prevent deficiency-related issues.', 24, 'Goat', 'Kid (0–6 months)'),
(27, 'Alfalfa Pellets', 1800.00, './Uploads/products/1752944156_e8036fa0-febc-44a8-9b74-70206277bb7c__37500-removebg-preview.png', '2025-07-19 16:55:56', 'Alfalfa Pellets are nutrient-rich, high-protein pellets made from premium sun-cured alfalfa hay. These pellets provide essential nutrients to support the rapid growth, muscle development, and overall health of young goats aged 6 to 12 months, including both doelings (females) and bucklings (males).', 25, 'Goat', 'Doeling/Buckling (6–12 months)'),
(28, 'Iron ', 2500.00, './Uploads/products/1752944258_1996994-removebg-preview.png', '2025-07-19 16:57:38', 'Iron is an essential trace mineral critical for the production of hemoglobin, which transports oxygen in the blood. Adequate iron levels in young goats ensure healthy growth, strong immune function, and prevention of anemia, especially in rapidly growing doelings and bucklings between 6 and 12 months of age.', 27, 'Goat', 'Doeling/Buckling (6–12 months)'),
(29, 'Mixed Ration Pellets', 7500.00, './Uploads/products/1752945356_MBGP20-removebg-preview.png', '2025-07-19 17:15:56', 'Mixed Ration Pellets for adult goats provide a balanced blend of energy, protein, fiber, vitamins, and minerals to support daily maintenance, reproductive health, and optimal productivity. These pellets are ideal for does, bucks, and wethers raised for milk, meat, or breeding between the ages of 1 and 7 years.', 12, 'Goat', 'Adult Goat (1–7 years)'),
(30, 'Vitamin E ', 2000.00, './Uploads/products/1752945477_51aQIMV5dnL-removebg-preview.png', '2025-07-19 17:17:57', 'This nutritional gel combines Vitamin E and Selenium to support muscle function, immunity, and reproductive health in adult goats. It is particularly important in Selenium-deficient regions and for goats under stress, breeding, or high performance.', 10, 'Goat', 'Adult Goat (1–7 years)'),
(31, 'Senior Goat Pellets', 9000.00, './Uploads/products/1752945658_71lRsxbApkL._UF894_1000_QL80_-removebg-preview.png', '2025-07-19 17:20:58', 'Specially formulated for senior goats aged 7 years and above, Senior Goat Pellets support joint health, digestive efficiency, and immune function. As goats age, their nutritional needs shift—this formula provides easily digestible protein, essential vitamins and minerals, and added fiber to maintain health and body condition in older goats.', 50, 'Goat', 'Senior (7+ years)'),
(32, 'Vitamin C ', 2800.00, './Uploads/products/1752945761_vitandmin-goat-mineral-drench-2.5l.png', '2025-07-19 17:22:41', 'As goats age, their natural Vitamin C synthesis may decrease, especially under stress or illness. This Vitamin C supplement is designed to support the immune system, promote collagen production, and improve recovery and resilience in senior goats aged 7 years and above.', 14, 'Goat', 'Senior (7+ years)'),
(33, 'Starter Crumble', 1600.00, './Uploads/products/1752945888_81saETmJGnL-removebg-preview.png', '2025-07-19 17:24:48', 'Starter crumble is a nutrient-dense feed specially formulated for day-old to 6-week-old chicks, supporting rapid growth, immunity development, and bone formation. With a soft, easy-to-eat texture and balanced nutrition, it helps chicks get the best start in life.', 120, 'Hen', 'Chick (0–6 weeks)'),
(34, 'Vitamin A', 800.00, './Uploads/products/1752945965_img_ChickNVitamins_Product_Website_ca_1200x1500_895f9313-c6e2-4871-8b4c-f951d3002377-removebg-preview.png', '2025-07-19 17:26:05', 'Vitamin A is a vital fat-soluble nutrient that plays a key role in the growth, immunity, and vision development of young chicks. Since chicks are in a rapid growth phase from hatching to 6 weeks, sufficient levels of Vitamin A help ensure strong tissue formation and resistance against common infections.', 58, 'Hen', 'Chick (0–6 weeks)'),
(35, 'Grower Pellets', 1700.00, './Uploads/products/1752946059_Nutrena-NatureWise-Hearty-Hen-FRONT_40lb_91057-768x922-removebg-preview.png', '2025-07-19 17:27:39', 'Grower pellets are a nutritionally balanced feed designed for pullets (young hens) and cockerels (young roosters) during the critical growth phase between 6 to 20 weeks of age. These pellets support skeletal development, muscle growth, and prepare birds for healthy adult life without the excess calcium found in layer feeds (which can harm kidneys at this age).', 48, 'Hen', 'Pullet/Cockerel (6–20 weeks)'),
(36, 'Calcium', 1500.00, './Uploads/products/1752946141_613RXZ-jRtL-removebg-preview.png', '2025-07-19 17:29:01', 'This calcium supplement is specially formulated to support bone development and muscle function in growing pullets and cockerels. While not as high in calcium as layer supplements, it provides just the right balance needed during the pre-laying stage to avoid calcium deficiency without overloading their kidneys.', 59, 'Hen', 'Pullet/Cockerel (6–20 weeks)'),
(37, 'Layer Pellets', 1900.00, './Uploads/products/1752946248_248639-removebg-preview.png', '2025-07-19 17:30:48', 'Layer pellets are a nutritionally complete feed formulated to support egg production, shell quality, and overall health in adult laying hens and breeding roosters. These pellets provide a balanced ratio of protein, calcium, and essential vitamins/minerals, making them ideal for chickens in their productive phase.', 50, 'Hen', 'Hen/Rooster (5–24 months+)'),
(38, 'Vitamin D3', 2500.00, './Uploads/products/1752946423_d-3-liquid-calcium-supplement-for-chickens-and-poultry-250ml__12768-removebg-preview.png', '2025-07-19 17:33:43', 'Vitamin D3 is an essential nutrient for egg-laying hens and active roosters, supporting calcium absorption, strong eggshell formation, and healthy bone development. This supplement helps prevent rickets, thin-shelled eggs, and skeletal issues in adult poultry.', 23, 'Hen', 'Hen/Rooster (5–24 months+)'),
(39, 'Senior Layer Pellets', 1800.00, './Uploads/products/1752946548_heygates-country-layers-meal-mash__20935-removebg-preview.png', '2025-07-19 17:35:48', 'Senior Layer Pellets are specially formulated to meet the changing nutritional needs of hens aged 2.5 years and above. These pellets support continued egg production, improved shell quality, and overall health maintenance during the later laying stages when nutrient absorption may decline.', 51, 'Hen', 'Senior (2.5+ years)'),
(40, 'Vitamin E ', 1800.00, './Uploads/products/1752946671_selvit-e-option-a-1-copy-500x500-removebg-preview.png', '2025-07-19 17:37:51', 'The Vitamin E Pack is a specially formulated supplement aimed at boosting the immune system, supporting cellular health, and improving reproductive performance in senior hens. Vitamin E acts as a powerful antioxidant that helps reduce oxidative stress commonly experienced by aging birds.', 58, 'Hen', 'Senior (2.5+ years)');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `pet_type` varchar(50) DEFAULT NULL,
  `pet_age` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `customer_id`, `customer_name`, `product_id`, `product_name`, `pet_type`, `pet_age`, `quantity`, `amount`, `status`, `created_at`) VALUES
(1, NULL, 'Basith', 1, 'Calf Milk Replacer', 'Cow', 'Calf (0–6 months)', 1, 4500.00, 'Pending', '2025-07-19 16:58:13'),
(2, NULL, 'Basith', 2, 'Vitamin ADE', 'Cow', 'Calf (0–6 months)', 1, 3000.00, 'Pending', '2025-07-19 17:02:48'),
(3, NULL, 'Basith', 2, 'Vitamin ADE', 'Cow', 'Calf (0–6 months)', 1, 3000.00, 'Approved', '2025-07-19 17:04:38'),
(4, NULL, 'Basith', 1, 'Calf Milk Replacer', 'Cow', 'Calf (0–6 months)', 1, 4500.00, 'Approved', '2025-07-19 17:32:21'),
(5, NULL, 'Basith', 3, 'Forage-Based Pellets', 'Cow', 'Heifer/Young Bull (6–24 months)', 1, 5500.00, 'Approved', '2025-07-19 21:09:32'),
(6, NULL, 'aashik', 7, 'Senior Cattle Pellets', 'Cow', 'Senior (8+ years)', 1, 5800.00, 'Approved', '2025-07-19 21:18:18'),
(7, NULL, 'aashik', 38, 'Vitamin D3', 'Hen', 'Hen/Rooster (5–24 months+)', 1, 2500.00, 'Approved', '2025-07-20 11:18:20'),
(8, NULL, 'Basith', 2, 'Vitamin ADE', 'Cow', 'Calf (0–6 months)', 1, 3000.00, 'Approved', '2025-07-20 13:43:16');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `status` enum('registered','active','inactive') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `address`, `profile_photo`, `created_at`, `first_name`, `last_name`, `phone_number`, `status`) VALUES
(1, 'aashik', 'Aashik@gmail.com', '$2y$10$4rhtazfG1AvES1RWIbpUk.o7CvofNgfSmyNr8Lt/g4nD8XeV8DyJG', '', './Uploads/profiles/684c252ba51d8-front-view-man-posing (1).jpg', '2025-06-13 12:19:00', 'Aaashik', 'Ahamad ', '07641000', 'inactive'),
(2, 'Basith', 'bas@gmail.com', '$2y$10$UACpUktBvdOLLj40Jic5J.UHwrJWjx7S5mypD.UripdhWmYduUi92', NULL, 'Uploads/1752924817_ChatGPT Image Jul 8, 2025, 07_05_36 PM.png', '2025-06-23 16:22:14', 'Mohamad', 'Ahamad ', '0764100000', 'registered'),
(3, 'Seya', 'sag@gmail.com', '$2y$10$U2qYO91P47fzV/jFfcIX5uIjfNdOfAFsBHY0Ja3fo6h.DDTBQQRhW', NULL, NULL, '2025-07-17 09:06:07', NULL, NULL, NULL, 'registered'),
(4, 'Induwara', 'indu@gmail.com', '$2y$10$ARmGdNcVeZl3ADGHe2p2EeqedMkjVJoDylZC2hV1/6KZMsWWLDQa6', NULL, NULL, '2025-07-17 09:16:42', NULL, NULL, NULL, 'registered'),
(5, 'Timothy', 'tim@gmail.com', '$2y$10$HId5J.JX7bk1SiBiDHyiQeVBtt7tINKTZuTrj2BgKmOC3G29ub7wa', NULL, NULL, '2025-07-19 17:43:22', NULL, NULL, NULL, 'registered');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
