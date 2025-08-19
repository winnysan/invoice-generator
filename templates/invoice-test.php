<?php
declare(strict_types=1);

/** @var array $data */
?>
<!DOCTYPE html>
<html lang="sk">
<head>
  <meta charset="UTF-8">
  <title>Faktúra č. <?= htmlspecialchars((string)($data['faktura_cislo'] ?? '')) ?></title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.4; }
    h1 { margin-bottom: 5px; }
    .flex { display: flex; justify-content: space-between; }
    .block { margin-bottom: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; }
    th { background: #eee; }
    .right { text-align: right; }
  </style>
</head>
<body>
  <h1>Faktúra č. <?= htmlspecialchars((string)($data['faktura_cislo'] ?? '')) ?></h1>

  <div class="flex block">
    <div>
      <strong>Dodávateľ</strong><br>
      <?= htmlspecialchars($data['dodavatel']['meno'] ?? '') ?><br>
      <?php foreach (($data['dodavatel']['adresa'] ?? []) as $line): ?>
        <?= htmlspecialchars($line) ?><br>
      <?php endforeach; ?>
      IČO: <?= htmlspecialchars((string)($data['dodavatel']['ico'] ?? '')) ?><br>
      DIČ: <?= htmlspecialchars((string)($data['dodavatel']['dic'] ?? '')) ?><br>
      IČ DPH: <?= $data['dodavatel']['ic_dph'] !== '' ? htmlspecialchars($data['dodavatel']['ic_dph']) : 'neplatiteľ DPH' ?><br>
      <?php if (!empty($data['dodavatel']['poznamka'])): ?>
        <div><?= htmlspecialchars($data['dodavatel']['poznamka']) ?></div>
      <?php endif; ?>
    </div>

    <div>
      <strong>Odberateľ</strong><br>
      <?= htmlspecialchars($data['odberatel']['meno'] ?? '') ?><br>
      <?php foreach (($data['odberatel']['adresa'] ?? []) as $line): ?>
        <?= htmlspecialchars($line) ?><br>
      <?php endforeach; ?>
      IČO: <?= htmlspecialchars((string)($data['odberatel']['ico'] ?? '')) ?><br>
      DIČ: <?= htmlspecialchars((string)($data['odberatel']['dic'] ?? '')) ?><br>
      IČ DPH: <?= $data['odberatel']['ic_dph'] !== '' ? htmlspecialchars($data['odberatel']['ic_dph']) : 'neplatiteľ DPH' ?><br>
      <?php if (!empty($data['odberatel']['poznamka'])): ?>
        <div><?= htmlspecialchars($data['odberatel']['poznamka']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="block">
    <strong>Platobné údaje</strong><br>
    IBAN: <?= htmlspecialchars($data['platobne_udaje']['iban'] ?? '') ?><br>
    SWIFT: <?= htmlspecialchars($data['platobne_udaje']['swift'] ?? '') ?><br>
    Forma úhrady: <?= htmlspecialchars($data['platobne_udaje']['forma_uhrady'] ?? '') ?><br>
    Variabilný symbol: <?= htmlspecialchars($data['platobne_udaje']['variabilny_symbol'] ?? '') ?><br>
  </div>

  <div class="block">
    <strong>Dátumy</strong><br>
    Dodanie: <?= htmlspecialchars($data['datumy']['datum_dodania'] ?? '') ?><br>
    Vystavenie: <?= htmlspecialchars($data['datumy']['datum_vystavenia'] ?? '') ?><br>
    Splatnosť: <?= htmlspecialchars($data['datumy']['datum_splatnosti'] ?? '') ?><br>
  </div>

  <?php if (!empty($data['dodacia_adresa'])): ?>
    <div class="block">
      <strong>Dodacia adresa</strong><br>
      <?php foreach ($data['dodacia_adresa'] as $line): ?>
        <?= htmlspecialchars($line) ?><br>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($data['poznamka'])): ?>
    <div class="block">
      <strong>Poznámka:</strong><br>
      <?= htmlspecialchars($data['poznamka']) ?>
    </div>
  <?php endif; ?>

  <div class="block">
    <strong>Položky</strong>
    <table>
      <thead>
        <tr>
          <th>Popis</th>
          <th>Počet</th>
          <th>Jedn. cena</th>
          <th>DPH</th>
          <th>Spolu</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data['polozky'] ?? [] as $p): 
          $unit = ($p['jednotkova_cena'] ?? 0) / 100;
          $count = (int)($p['pocet'] ?? 0);
          $subtotal = $unit * $count;
          $taxRate = $p['dph'] ?? 0;
          $total = $subtotal * (1 + $taxRate);
        ?>
          <tr>
            <td><?= htmlspecialchars($p['popis'] ?? '') ?></td>
            <td class="right"><?= $count ?></td>
            <td class="right"><?= number_format($unit, 2, ',', ' ') . ' ' . ($p['mena'] ?? '') ?></td>
            <td class="right"><?= $taxRate ? (string)((float)$taxRate * 100) . ' %' : '-' ?></td>
            <td class="right"><?= number_format($total, 2, ',', ' ') . ' ' . ($p['mena'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="block">
    <strong>Sumár</strong><br>
    Cena bez DPH: <?= number_format((float)($data['cena_bez_dph'] ?? 0), 2, ',', ' ') ?> EUR<br>
    Cena s DPH: <?= number_format((float)($data['cena_s_dph'] ?? 0), 2, ',', ' ') ?> EUR<br>
    Celkom k úhrade: <strong><?= number_format((float)($data['celkom_k_uhrade'] ?? 0), 2, ',', ' ') ?> EUR</strong>
  </div>

  <div class="block">
    <strong>Vystavil:</strong> <?= htmlspecialchars($data['vystavil']['meno'] ?? '') ?>
  </div>
</body>
</html>
