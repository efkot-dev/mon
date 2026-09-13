<?php
if (!defined('PONMONITOR') && !defined('GENERATOR')) {
    die('Hacking attempt!');
}

$url_generator = "/?do=generator";

$types = [
    'diesel'=>['label'=>'Дизель','img'=>'../style/img/p2.png'],
    'gasoline'=>['label'=>'Бензин','img'=>'../style/img/p1.png'],
    'gas'=>['label'=>'Газ','img'=>'../style/img/p3.png']
];

function toDBDatetime($val){
    if(!$val) return null;
    return str_replace('T',' ',$val).':00';
}

function formatHoursMinutes($hours){
    $h=floor($hours);
    $m=round(($hours-$h)*60);
    return "{$h}г {$m}хв";
}
function listGeneratorsStart(PDO $pdo){
	$stmt = $pdo->query("SELECT g.* FROM generators g LEFT JOIN generator_runs gr ON gr.generator_id = g.id AND gr.end_at IS NULL WHERE gr.id IS NULL ORDER BY g.id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function addGenerator(PDO $pdo,$name,$fuel_type,$power,$fuel_consumption){
    return $pdo->prepare("
        INSERT INTO generators(name,fuel_type,power,fuel_consumption)
        VALUES(?,?,?,?)
    ")->execute([$name,$fuel_type,$power,$fuel_consumption]);
}

function updateGenerator(PDO $pdo,$id,$name,$fuel_type,$power,$fuel_consumption){
    return $pdo->prepare("
        UPDATE generators
        SET name=?,fuel_type=?,power=?,fuel_consumption=?
        WHERE id=?
    ")->execute([$name,$fuel_type,$power,$fuel_consumption,$id]);
}

##################################################
# START
##################################################

function startGenerator(PDO $pdo,int $generator_id,string $location,string $start_at):array{

    $g=$pdo->prepare("SELECT fuel_type,fuel_consumption FROM generators WHERE id=?");
    $g->execute([$generator_id]);
    $gen=$g->fetch(PDO::FETCH_ASSOC);

    if(!$gen) return ['ok'=>false,'msg'=>'Генератор не знайдено'];

    $fuel=$pdo->prepare("
        SELECT id FROM fuel_stock
        WHERE fuel_type=? AND quantity>0
        ORDER BY added_at LIMIT 1
    ");
    $fuel->execute([$gen['fuel_type']]);
    $pack=$fuel->fetch(PDO::FETCH_ASSOC);

    if(!$pack) return ['ok'=>false,'msg'=>'Немає палива'];

    $ok=$pdo->prepare("
        INSERT INTO generator_runs(generator_id,fuel_id,location,start_at,fuel_rate_at_run)
        VALUES(?,?,?,?,?)
    ")->execute([
        $generator_id,
        $pack['id'],
        $location,
        $start_at,
        $gen['fuel_consumption']
    ]);

    return ['ok'=>$ok];
}

##################################################
# STOP
##################################################
function stopGenerator(PDO $pdo,int $run_id,string $end_at):array
{
    try{
        $pdo->beginTransaction();

        $stmt=$pdo->prepare("
            SELECT gr.*,g.fuel_type,g.fuel_consumption
            FROM generator_runs gr
            JOIN generators g ON g.id=gr.generator_id
            WHERE gr.id=?
        ");
        $stmt->execute([$run_id]);
        $run=$stmt->fetch(PDO::FETCH_ASSOC);

        if(!$run) throw new Exception("run not found");
        if($run['end_at']) throw new Exception("вже зупинено");

        $start=new DateTime($run['start_at']);
        $end=new DateTime($end_at);

        if($end <= $start) throw new Exception("Некоректний час зупинки");

        // точний час
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        $hours   = $seconds / 3600;

        // актуальний розхід генератора
        $rate = $run['fuel_consumption'];

        // точна витрата
        $fuel_needed = round($hours * $rate, 3);

        // списання FIFO
        $remaining = $fuel_needed;

        $packs=$pdo->prepare("
            SELECT * FROM fuel_stock
            WHERE fuel_type=? AND quantity>0
            ORDER BY added_at
        ");
        $packs->execute([$run['fuel_type']]);

        foreach($packs as $pack){
            if($remaining <= 0) break;

            $use = min($pack['quantity'], $remaining);

            $pdo->prepare("
                UPDATE fuel_stock SET quantity = quantity - ?
                WHERE id=?
            ")->execute([$use,$pack['id']]);

            $pdo->prepare("
                INSERT INTO fuel_history(fuel_id,quantity,action,related_run)
                VALUES(?,?,'used',?)
            ")->execute([$pack['id'],$use,$run_id]);

            $remaining -= $use;
        }

        if($remaining > 0)
            throw new Exception("Недостатньо палива");

        // запис результату
        $pdo->prepare("
            UPDATE generator_runs
            SET end_at=?, hours_run=?, fuel_used=?, fuel_rate_at_run=?
            WHERE id=?
        ")->execute([
            $end_at,
            round($hours,5),
            $fuel_needed,
            $rate,
            $run_id
        ]);

        $pdo->commit();
        return ['ok'=>true];

    }catch(Exception $e){
        $pdo->rollBack();
        return ['ok'=>false,'msg'=>$e->getMessage()];
    }
}
function recalcGeneratorFuel(PDO $pdo,int $generator_id):array
{
    try{
        $pdo->beginTransaction();

        // розхід генератора
        $stmt=$pdo->prepare("SELECT fuel_consumption,fuel_type FROM generators WHERE id=?");
        $stmt->execute([$generator_id]);
        $gen=$stmt->fetch(PDO::FETCH_ASSOC);

        if(!$gen) throw new Exception("Генератор не знайдено");

        $rate=(float)$gen['fuel_consumption'];
        $fuel_type=$gen['fuel_type'];

        // всі завершені запуски
        $runs=$pdo->prepare("
            SELECT id,start_at,end_at
            FROM generator_runs
            WHERE generator_id=? AND end_at IS NOT NULL
            ORDER BY start_at
        ");
        $runs->execute([$generator_id]);
        $runs=$runs->fetchAll(PDO::FETCH_ASSOC);

        // =====================================================
        // 1. ПОВЕРНУТИ СТАРЕ ПАЛИВО В СКЛАД
        // =====================================================

        $old_used=$pdo->prepare("
            SELECT fuel_id, quantity
            FROM fuel_history
            WHERE action='used'
            AND related_run IN (
                SELECT id FROM generator_runs WHERE generator_id=?
            )
        ");
        $old_used->execute([$generator_id]);

        foreach($old_used as $u){
            $pdo->prepare("
                UPDATE fuel_stock
                SET quantity = quantity + ?
                WHERE id=?
            ")->execute([$u['quantity'],$u['fuel_id']]);
        }

        // =====================================================
        // 2. ВИДАЛИТИ СТАРІ СПИСАННЯ
        // =====================================================

        $pdo->prepare("
            DELETE FROM fuel_history
            WHERE action='used'
            AND related_run IN (
                SELECT id FROM generator_runs WHERE generator_id=?
            )
        ")->execute([$generator_id]);

        // =====================================================
        // 3. ПЕРЕРАХУВАТИ КОЖЕН RUN І СПИСАТИ FIFO
        // =====================================================

        foreach($runs as $r){

            $start=new DateTime($r['start_at']);
            $end=new DateTime($r['end_at']);

            $hours=($end->getTimestamp()-$start->getTimestamp())/3600;
            $fuel_needed=round($hours*$rate,3);

            $remaining=$fuel_needed;

            $packs=$pdo->prepare("
                SELECT * FROM fuel_stock
                WHERE fuel_type=? AND quantity>0
                ORDER BY added_at
            ");
            $packs->execute([$fuel_type]);

            foreach($packs as $pack){

                if($remaining<=0) break;

                $use=min($pack['quantity'],$remaining);

                // списати
                $pdo->prepare("
                    UPDATE fuel_stock
                    SET quantity = quantity - ?
                    WHERE id=?
                ")->execute([$use,$pack['id']]);

                // запис history
                $pdo->prepare("
                    INSERT INTO fuel_history(fuel_id,quantity,action,related_run)
                    VALUES(?,?,'used',?)
                ")->execute([$pack['id'],$use,$r['id']]);

                $remaining-=$use;
            }

            if($remaining>0)
                throw new Exception("Недостатньо палива при перерахунку");

            // оновити run
            $pdo->prepare("
                UPDATE generator_runs
                SET hours_run=?, fuel_used=?, fuel_rate_at_run=?
                WHERE id=?
            ")->execute([
                round($hours,5),
                $fuel_needed,
                $rate,
                $r['id']
            ]);
        }

        $pdo->commit();
        return ['ok'=>true];

    }catch(Exception $e){
        $pdo->rollBack();
        return ['ok'=>false,'msg'=>$e->getMessage()];
    }
}


function recalcFuelStockFull(PDO $pdo){
    $pdo->exec("UPDATE fuel_stock SET quantity=0");
    $added = $pdo->query("
        SELECT fuel_id, SUM(quantity) q
        FROM fuel_history
        WHERE action='added'
        GROUP BY fuel_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($added as $a) {
        $pdo->prepare("
            UPDATE fuel_stock
            SET quantity = quantity + ?
            WHERE id=?
        ")->execute([$a['q'], $a['fuel_id']]);
    }
    $used = $pdo->query("
        SELECT fuel_id, SUM(quantity) q
        FROM fuel_history
        WHERE action='used'
        GROUP BY fuel_id
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($used as $u) {
        $pdo->prepare("
            UPDATE fuel_stock
            SET quantity = quantity - ?
            WHERE id=?
        ")->execute([$u['q'], $u['fuel_id']]);
    }
}

function addFuelStock(PDO $pdo, $fuel_type, $quantity){
    $stmt = $pdo->prepare("INSERT INTO fuel_stock (fuel_type, quantity) VALUES (?, ?)");
    if($stmt->execute([$fuel_type, $quantity])){
        $id = $pdo->lastInsertId();
        $h = $pdo->prepare("INSERT INTO fuel_history (fuel_id, quantity, action) VALUES (?, ?, 'added')");
        $h->execute([$id, $quantity]);
        return true;
    }
    return false;
}
function fuelChartMonth(PDO $pdo,$generator_id,$month){

    $stmt=$pdo->prepare("
        SELECT DATE(start_at) d, SUM(fuel_used) total
        FROM generator_runs
        WHERE generator_id=?
        AND DATE_FORMAT(start_at,'%Y-%m')=?
        GROUP BY DATE(start_at)
        ORDER BY d
    ");
    $stmt->execute([$generator_id,$month]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function listGenerators(PDO $pdo){ $stmt = $pdo->query("SELECT * FROM generators ORDER BY id DESC"); return $stmt->fetchAll(PDO::FETCH_ASSOC); }
function getGenerator(PDO $pdo, $id){ $stmt = $pdo->prepare("SELECT * FROM generators WHERE id=?"); $stmt->execute([$id]); return $stmt->fetch(PDO::FETCH_ASSOC); } 
function listFuelStock(PDO $pdo){ $stmt = $pdo->query("SELECT * FROM fuel_stock ORDER BY id DESC"); return $stmt->fetchAll(PDO::FETCH_ASSOC); } 
function getFuelStock(PDO $pdo, $id){ $stmt = $pdo->prepare("SELECT * FROM fuel_stock WHERE id=?"); $stmt->execute([$id]); return $stmt->fetch(PDO::FETCH_ASSOC); }
function totalFuelByType(PDO $pdo){ $stmt = $pdo->query("SELECT fuel_type, SUM(quantity) AS total FROM fuel_stock GROUP BY fuel_type"); return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);}
function listActiveRuns(PDO $pdo){ $stmt = $pdo->query("SELECT gr.*, g.name AS generator_name, g.fuel_consumption, fs.fuel_type, fs.quantity as fuel_packet_quantity FROM generator_runs gr JOIN generators g ON gr.generator_id = g.id JOIN fuel_stock fs ON gr.fuel_id = fs.id WHERE gr.end_at IS NULL ORDER BY gr.start_at DESC"); return $stmt->fetchAll(PDO::FETCH_ASSOC); } 
function runsByGenerator(PDO $pdo, $generator_id){ $stmt = $pdo->prepare("SELECT gr.*, fs.fuel_type, fs.id as fuel_stock_id FROM generator_runs gr LEFT JOIN fuel_stock fs ON gr.fuel_id = fs.id WHERE gr.generator_id = ? ORDER BY gr.start_at DESC"); $stmt->execute([$generator_id]); return $stmt->fetchAll(PDO::FETCH_ASSOC); } 
function fuelHistory(PDO $pdo, $fuel_id){ $stmt = $pdo->prepare("SELECT * FROM fuel_history WHERE fuel_id=? ORDER BY created_at DESC"); $stmt->execute([$fuel_id]); return $stmt->fetchAll(PDO::FETCH_ASSOC); } 
